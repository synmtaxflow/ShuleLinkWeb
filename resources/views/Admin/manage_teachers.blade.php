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
    #phone_number.is-valid {
        border-color: #28a745;
    }
    #phone_number.is-invalid {
        border-color: #dc3545;
    }
    #phone_error {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
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
                <i class="bi bi-people-fill"></i> Manage Teachers
            </h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary-custom fw-bold" id="manageRolesBtn" type="button">
                        <i class="bi bi-shield-check"></i> Manage Roles & Permissions
                    </button>
                    <button class="btn btn-outline-primary-custom fw-bold" id="viewTeachersRolesBtn" type="button" data-bs-toggle="modal" data-bs-target="#viewTeachersRolesModal">
                        <i class="bi bi-eye"></i> View Teachers Roles
                    </button>
                    <button class="btn btn-outline-primary-custom fw-bold" id="assignRoleBtn" type="button" data-bs-toggle="modal" data-bs-target="#assignRoleModal">
                        <i class="bi bi-person-badge"></i> Assign Roles
                    </button>
                    <button class="btn btn-outline-primary-custom fw-bold" id="addTeacherBtn" type="button" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                <i class="bi bi-person-plus"></i> Add New Teacher
            </button>
                    <button class="btn btn-outline-primary-custom fw-bold" id="viewTeacherAttendanceBtn" type="button" data-bs-toggle="modal" data-bs-target="#teacherAttendanceModal">
                <i class="bi bi-calendar-check"></i> Teacher Attendance
            </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
        <div class="table-responsive">
                <table id="teachersTable" class="table table-hover align-middle mb-0" style="width:100%">
                    <thead>
                    <tr>
                        <th>Image</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Fingerprint ID</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($teachers) > 0)
                    @foreach ($teachers as $index => $teacher)
                        <tr data-teacher-id="{{ $teacher->id }}">
                            <td>
                                @php
                                    $imgPath = $teacher->image
                                        ? asset('userImages/' . $teacher->image)
                                        : ($teacher->gender == 'Female'
                                            ? asset('images/female.png')
                                            : asset('images/male.png'));
                                @endphp
                                    <img src="{{ $imgPath }}" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover; border: 3px solid #940000;" alt="Teacher">
                            </td>
                            <td><strong>{{ $teacher->first_name }} {{ $teacher->middle_name }} {{ $teacher->last_name }}</strong></td>
                            <td>{{ $teacher->email }}</td>
                            <td>{{ $teacher->phone_number }}</td>
                            <td>
                                @if($teacher->fingerprint_id)
                                    <span class="badge bg-info text-white" style="font-size: 0.9rem; font-weight: bold;">
                                        <i class="bi bi-fingerprint"></i> {{ $teacher->fingerprint_id }}
                                    </span>
                                @else
                                    <span class="text-muted" style="font-style: italic;">
                                        <i class="bi bi-dash-circle"></i> Not assigned
                                    </span>
                                @endif
                            </td>
                            <td>
                                    <span class="badge {{ strtolower($teacher->status) == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $teacher->status }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center align-items-center gap-2">
                                        <a href="#" class="btn btn-sm btn-info text-white view-teacher-btn"
                                       data-teacher-id="{{ $teacher->id }}"
                                       title="View Details">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                        <a href="#" class="btn btn-sm btn-warning text-dark edit-teacher-btn"
                                           data-teacher-id="{{ $teacher->id }}"
                                           title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                        <a href="#" class="btn btn-sm btn-success text-white send-to-fingerprint-btn"
                                           data-teacher-id="{{ $teacher->id }}"
                                           data-teacher-name="{{ $teacher->first_name }}"
                                           title="Send to Fingerprint Device">
                                        <i class="bi bi-fingerprint"></i>
                                    </a>
                                        <a href="#" class="btn btn-sm btn-danger text-white" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                            <!-- Hidden data for View More - stored in data attribute -->
                            <div style="display:none;" class="teacher-full-details" data-teacher-id="{{ $teacher->id }}">
                                @php
                                    $teacherImgPath = $teacher->image
                                        ? asset('userImages/' . $teacher->image)
                                        : ($teacher->gender == 'Female'
                                            ? asset('images/female.png')
                                            : asset('images/male.png'));
                                @endphp
                                    <div class="p-3">
                                    <!-- Teacher Details Card (like manage_school) -->
                                        <div class="school-details-card">
                                            <div class="school-header">
                                                <div class="d-flex align-items-center">
                                                    <div class="school-logo-preview me-3">
                                                        <img src="{{ $teacherImgPath }}" alt="{{ $teacher->first_name }} {{ $teacher->last_name }}">
                                                    </div>
                                                    <div>
                                                        <h3 class="school-title">{{ $teacher->first_name }} {{ $teacher->middle_name }} {{ $teacher->last_name }}</h3>
                                                        <small class="text-muted">Employee: {{ $teacher->employee_number }}</small>
                                                    </div>
                                                </div>
                                                <span class="badge {{ strtolower($teacher->status) == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $teacher->status }}
                                                </span>
                                            </div>

                                            <!-- Teacher Info Grid (like manage_school) -->
                                            <div class="school-info-grid">
                                                <div class="info-item">
                                                    <i class="bi bi-gender-ambiguous"></i>
                                                    <div class="info-item-content">
                                                        <div class="info-item-label">Gender</div>
                                                        <div class="info-item-value">{{ $teacher->gender }}</div>
                                                    </div>
                                                </div>
                                                
                                                @if($teacher->position)
                                                <div class="info-item">
                                                    <i class="bi bi-briefcase"></i>
                                                    <div class="info-item-content">
                                                        <div class="info-item-label">Position</div>
                                                        <div class="info-item-value">{{ $teacher->position }}</div>
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                <div class="info-item">
                                                    <i class="bi bi-card-text"></i>
                                                    <div class="info-item-content">
                                                        <div class="info-item-label">National ID</div>
                                                        <div class="info-item-value">{{ $teacher->national_id }}</div>
                                                    </div>
                                                </div>
                                                
                                                <div class="info-item">
                                                    <i class="bi bi-person-badge"></i>
                                                    <div class="info-item-content">
                                                        <div class="info-item-label">Employee Number</div>
                                                        <div class="info-item-value">{{ $teacher->employee_number }}</div>
                                                    </div>
                                                </div>
                                                
                                                <div class="info-item">
                                                    <i class="bi bi-envelope"></i>
                                                    <div class="info-item-content">
                                                        <div class="info-item-label">Email</div>
                                                        <div class="info-item-value">{{ $teacher->email }}</div>
                                                    </div>
                                                </div>
                                                
                                                <div class="info-item">
                                                    <i class="bi bi-telephone"></i>
                                                    <div class="info-item-content">
                                                        <div class="info-item-label">Phone Number</div>
                                                        <div class="info-item-value">{{ $teacher->phone_number }}</div>
                                                    </div>
                                                </div>
                                                
                                                @if($teacher->qualification)
                                                <div class="info-item">
                                                    <i class="bi bi-mortarboard"></i>
                                                    <div class="info-item-content">
                                                        <div class="info-item-label">Qualification</div>
                                                        <div class="info-item-value">{{ $teacher->qualification }}</div>
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                @if($teacher->specialization)
                                                <div class="info-item">
                                                    <i class="bi bi-book"></i>
                                                    <div class="info-item-content">
                                                        <div class="info-item-label">Specialization</div>
                                                        <div class="info-item-value">{{ $teacher->specialization }}</div>
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                @if($teacher->experience)
                                                <div class="info-item">
                                                    <i class="bi bi-clock-history"></i>
                                                    <div class="info-item-content">
                                                        <div class="info-item-label">Experience</div>
                                                        <div class="info-item-value">{{ $teacher->experience }}</div>
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                @if($teacher->date_of_birth)
                                                <div class="info-item">
                                                    <i class="bi bi-calendar-event"></i>
                                                    <div class="info-item-content">
                                                        <div class="info-item-label">Date of Birth</div>
                                                        <div class="info-item-value">{{ date('d M Y', strtotime($teacher->date_of_birth)) }}</div>
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                @if($teacher->date_hired)
                                                <div class="info-item">
                                                    <i class="bi bi-calendar-check"></i>
                                                    <div class="info-item-content">
                                                        <div class="info-item-label">Date Hired</div>
                                                        <div class="info-item-value">{{ date('d M Y', strtotime($teacher->date_hired)) }}</div>
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                @if($teacher->address)
                                                <div class="info-item">
                                                    <i class="bi bi-geo-alt"></i>
                                                    <div class="info-item-content">
                                                        <div class="info-item-label">Address</div>
                                                        <div class="info-item-value">{{ $teacher->address }}</div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                            </div>
                                        </div>
                                    </div>
                            </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
                                </div>
                            </div>
    </div>

</div>

{{-- View Teachers Roles Modal --}}
<div class="modal fade" id="viewTeachersRolesModal" tabindex="-1" aria-labelledby="viewTeachersRolesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 95%; width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewTeachersRolesModalLabel">
                    <i class="bi bi-person-badge"></i> Teachers Roles
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="teachersRolesTable" class="table table-hover table-striped align-middle mb-0" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>Image</th>
                                <th>Teacher Name</th>
                                <th>Employee Number</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($teachersWithRoles) > 0)
                            @foreach ($teachersWithRoles as $teacherRole)
                                <tr>
                                    <td>
                                        @php
                                            $imgPath = $teacherRole->image
                                                ? asset('userImages/' . $teacherRole->image)
                                                : ($teacherRole->gender == 'Female'
                                                    ? asset('images/female.png')
                                                    : asset('images/male.png'));
                                        @endphp
                                        <img src="{{ $imgPath }}" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover; border: 3px solid #940000;" alt="Teacher">
                                    </td>
                                    <td><strong>{{ $teacherRole->first_name }} {{ $teacherRole->middle_name }} {{ $teacherRole->last_name }}</strong></td>
                                    <td>{{ $teacherRole->employee_number }}</td>
                                    <td>{{ $teacherRole->email }}</td>
                                    <td>
                                        <span class="badge bg-primary-custom text-white fs-6 px-3 py-2">
                                            {{ $teacherRole->role_name }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-warning text-dark change-role-btn"
                                                    data-role-user-id="{{ $teacherRole->role_user_id }}"
                                                    data-role-id="{{ $teacherRole->role_id }}"
                                                    data-role-name="{{ $teacherRole->role_name }}"
                                                    data-current-teacher-id="{{ $teacherRole->teacher_id }}"
                                                    data-current-teacher-name="{{ $teacherRole->first_name }} {{ $teacherRole->last_name }}"
                                                    title="Change Role">
                                                <i class="bi bi-arrow-repeat"></i> Change Role
                                            </button>
                                            <button class="btn btn-sm btn-danger remove-role-btn"
                                                    data-role-user-id="{{ $teacherRole->role_user_id }}"
                                                    data-role-id="{{ $teacherRole->role_id }}"
                                                    data-role-name="{{ $teacherRole->role_name }}"
                                                    data-teacher-id="{{ $teacherRole->teacher_id }}"
                                                    data-teacher-name="{{ $teacherRole->first_name }} {{ $teacherRole->middle_name }} {{ $teacherRole->last_name }}"
                                                    title="Remove Role">
                                                <i class="bi bi-x-circle"></i> Remove
                                            </button>
                                        </div>
                                    </td>
                        </tr>
                    @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="bi bi-inbox" style="font-size: 48px; color: #940000;"></i>
                                        <p class="mt-3 mb-0 text-muted">No teachers with assigned roles found. Click 'Assign Roles' to assign roles to teachers.</p>
                                    </td>
                                </tr>
                    @endif
                </tbody>
            </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Assign Role Modal --}}
<div class="modal fade" id="assignRoleModal" tabindex="-1" aria-labelledby="assignRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="assignRoleModalLabel">
                    <i class="bi bi-person-badge"></i> Assign Role to Teacher
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignRoleForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Teacher <span class="text-danger">*</span></label>
                        <select name="teacher_id" id="teacher_select" class="form-select" required>
                            <option value="">Choose a teacher...</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" data-email="{{ $teacher->email }}">
                                    {{ $teacher->first_name }} {{ $teacher->middle_name }} {{ $teacher->last_name }} ({{ $teacher->employee_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Role <span class="text-danger">*</span></label>
                        <select name="role_id" id="role_select" class="form-select" required>
                            <option value="">Choose a role...</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Assign Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Teacher Modal --}}
<div class="modal fade" id="addTeacherModal" tabindex="-1" aria-labelledby="addTeacherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
        @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
            @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
            <form id="teacherForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary-custom text-white">
                    <h5 class="modal-title" id="addTeacherModalLabel">
                        <i class="bi bi-person-plus-fill"></i> Add New Teacher
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" id="addTeacherModalBody" style="max-height: 70vh; overflow-y: auto; overflow-x: hidden;">
                    <style>
                        /* Show scrollbar for all browsers */
                        #addTeacherModalBody {
                            overflow-y: auto !important;
                            overflow-x: hidden !important;
                            scrollbar-width: thin; /* Firefox - thin scrollbar */
                            scrollbar-color: #cbd5e0 #f7fafc; /* Firefox - thumb and track colors */
                        }
                        
                        /* Style scrollbar for Chrome, Safari, Opera */
                        #addTeacherModalBody::-webkit-scrollbar {
                            width: 8px;
                            height: 8px;
                        }
                        
                        #addTeacherModalBody::-webkit-scrollbar-track {
                            background: #f1f1f1;
                            border-radius: 10px;
                        }
                        
                        #addTeacherModalBody::-webkit-scrollbar-thumb {
                            background: #888;
                            border-radius: 10px;
                        }
                        
                        #addTeacherModalBody::-webkit-scrollbar-thumb:hover {
                            background: #555;
                        }
                    </style>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Gender <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="phone_number"
                                   class="form-control"
                                   id="phone_number"
                                   pattern="^255\d{9}$"
                                   placeholder="255614863345"
                                   required
                                   maxlength="12">
                            <small class="text-muted">Must start with 255 followed by 9 digits (12 digits total, e.g., 255614863345)</small>
                            <div class="invalid-feedback" id="phone_error" style="display: none;">
                                Phone number must have 12 digits: start with 255 followed by 9 digits
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">National ID <span class="text-danger">*</span></label>
                            <input type="text" name="national_id" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Employee Number <span class="text-danger">*</span></label>
                            <input type="text" name="employee_number" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Qualification</label>
                            <input type="text" name="qualification" class="form-control" placeholder="e.g., Bachelor's Degree">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Specialization</label>
                            <input type="text" name="specialization" class="form-control" placeholder="e.g., Mathematics">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Experience</label>
                            <input type="text" name="experience" class="form-control" placeholder="e.g., 5 years">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Date Hired</label>
                            <input type="date" name="date_hired" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Position</label>
                            <input type="text" name="position" class="form-control" placeholder="e.g., Senior Teacher">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="On Leave">On Leave</option>
                                <option value="Retired">Retired</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Address</label>
                            <input type="text" name="address" class="form-control" placeholder="Full address">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Teacher Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Supported formats: JPG, PNG (Max: 2MB)</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="display: flex !important;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom" style="display: inline-block !important;">
                        <i class="bi bi-save"></i> Save Teacher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Change Role Modal --}}
<div class="modal fade" id="changeRoleModal" tabindex="-1" aria-labelledby="changeRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="changeRoleModalLabel">
                    <i class="bi bi-arrow-repeat"></i> Change Role Assignment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changeRoleForm">
                @csrf
                <input type="hidden" name="role_user_id" id="change_role_user_id">
                <input type="hidden" name="role_id" id="change_role_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Current Role:</strong> <span id="change_current_role_name"></span><br>
                        <strong>Current Teacher:</strong> <span id="change_current_teacher_name"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select New Teacher <span class="text-danger">*</span></label>
                        <select name="new_teacher_id" id="change_new_teacher_select" class="form-select" required>
                            <option value="">Choose a teacher...</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">
                                    {{ $teacher->first_name }} {{ $teacher->middle_name }} {{ $teacher->last_name }} ({{ $teacher->employee_number }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select a different teacher to assign this role to.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-arrow-repeat"></i> Change Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Teacher Modal --}}
<div class="modal fade" id="editTeacherModal" tabindex="-1" aria-labelledby="editTeacherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="editTeacherForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="teacher_id" id="edit_teacher_id">
                <div class="modal-header bg-primary-custom text-white">
                    <h5 class="modal-title" id="editTeacherModalLabel">
                        <i class="bi bi-pencil-square"></i> Edit Teacher
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" id="editTeacherModalBody" style="max-height: 70vh; overflow-y: auto; overflow-x: hidden;">
                    <style>
                        /* Hide scrollbar but allow scrolling */
                        #editTeacherModalBody {
                            overflow-y: auto !important;
                            overflow-x: hidden !important;
                            scrollbar-width: none; /* Firefox - hide scrollbar */
                            -ms-overflow-style: none; /* IE and Edge - hide scrollbar */
                        }
                        
                        /* Hide scrollbar for Chrome, Safari, Opera */
                        #editTeacherModalBody::-webkit-scrollbar {
                            display: none;
                        }
                    </style>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Middle Name</label>
                            <input type="text" name="middle_name" id="edit_middle_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Gender <span class="text-danger">*</span></label>
                            <select name="gender" id="edit_gender" class="form-select" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="phone_number"
                                   class="form-control"
                                   id="edit_phone_number"
                                   pattern="^255\d{9}$"
                                   placeholder="255614863345"
                                   required
                                   maxlength="12">
                            <small class="text-muted">Must start with 255 followed by 9 digits (12 digits total, e.g., 255614863345)</small>
                            <div class="invalid-feedback" id="edit_phone_error" style="display: none;">
                                Phone number must have 12 digits: start with 255 followed by 9 digits
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">National ID <span class="text-danger">*</span></label>
                            <input type="text" name="national_id" id="edit_national_id" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Employee Number <span class="text-danger">*</span></label>
                            <input type="text" name="employee_number" id="edit_employee_number" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Qualification</label>
                            <input type="text" name="qualification" id="edit_qualification" class="form-control" placeholder="e.g., Bachelor's Degree">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Specialization</label>
                            <input type="text" name="specialization" id="edit_specialization" class="form-control" placeholder="e.g., Mathematics">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Experience</label>
                            <input type="text" name="experience" id="edit_experience" class="form-control" placeholder="e.g., 5 years">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="edit_date_of_birth" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Date Hired</label>
                            <input type="date" name="date_hired" id="edit_date_hired" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Position</label>
                            <input type="text" name="position" id="edit_position" class="form-control" placeholder="e.g., Senior Teacher">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="On Leave">On Leave</option>
                                <option value="Retired">Retired</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Address</label>
                            <input type="text" name="address" id="edit_address" class="form-control" placeholder="Full address">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Teacher Image</label>
                            <input type="file" name="image" id="edit_image" class="form-control" accept="image/*">
                            <small class="text-muted">Supported formats: JPG, PNG (Max: 2MB). Leave empty to keep current image.</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
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

{{-- View Teacher Details Modal --}}
<div class="modal fade" id="viewTeacherModal" tabindex="-1" aria-labelledby="viewTeacherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 90%; width: 1100px;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewTeacherModalLabel">
                    <i class="bi bi-person-badge"></i> Teacher Profile
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="teacherDetailsContent">
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

{{-- Manage Roles & Permissions Modal --}}
<div class="modal fade" id="manageRolesModal" tabindex="-1" aria-labelledby="manageRolesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 95%; width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="manageRolesModalLabel">
                    <i class="bi bi-shield-check"></i> Manage Roles & Permissions
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="rolesPermissionsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="roles-tab" data-bs-toggle="tab" data-bs-target="#roles" type="button" role="tab">
                            <i class="bi bi-person-badge"></i> Roles
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="permissions-tab" data-bs-toggle="tab" data-bs-target="#permissions" type="button" role="tab">
                            <i class="bi bi-key"></i> Permissions
                        </button>
                    </li>
                </ul>

                <div class="tab-content mt-4" id="rolesPermissionsTabsContent">
                    <!-- Roles Tab -->
                    <div class="tab-pane fade show active" id="roles" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">All Roles</h6>
                            <button class="btn btn-sm btn-primary-custom" id="addRoleBtn">
                                <i class="bi bi-plus-circle"></i> Add New Role
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="rolesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Role Name</th>
                                        <th>Permissions</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($roles as $role)
                                        <tr>
                                            <td><strong>{{ $role->role_name ?? $role->name }}</strong></td>
                                            <td>
                                                @php
                                                    // Safely get permissions - check if it's a relationship or collection
                                                    $rolePermissions = collect();
                                                    if (method_exists($role, 'permissions')) {
                                                        try {
                                                            $perms = $role->permissions;
                                                            // Check if it's a relationship (BelongsToMany, HasMany, etc.)
                                                            if ($perms instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                                                                $rolePermissions = $perms->get();
                                                            } elseif ($perms instanceof \Illuminate\Support\Collection) {
                                                                // Already a collection, use it directly
                                                                $rolePermissions = $perms;
                                                            } elseif (is_array($perms)) {
                                                                $rolePermissions = collect($perms);
                                                            } elseif (is_object($perms) && method_exists($perms, 'toArray')) {
                                                                $rolePermissions = collect($perms->toArray());
                                                            } else {
                                                                $rolePermissions = collect();
                                                            }
                                                        } catch (\Exception $e) {
                                                            $rolePermissions = collect();
                                                        }
                                                    }
                                                @endphp
                                                @if($rolePermissions && $rolePermissions->count() > 0)
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($rolePermissions as $permission)
                                                            <span class="badge bg-info">{{ $permission->name ?? 'N/A' }}</span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted">No permissions</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-primary text-white edit-role-name-btn"
                                                            data-role-id="{{ $role->id }}"
                                                            data-role-name="{{ $role->role_name ?? $role->name }}">
                                                        <i class="bi bi-pencil-square"></i> Edit Name
                                                    </button>
                                                    <button class="btn btn-sm btn-warning text-dark edit-role-permissions-btn"
                                                            data-role-id="{{ $role->id }}"
                                                            data-role-name="{{ $role->role_name ?? $role->name }}">
                                                        <i class="bi bi-pencil"></i> Edit Permissions
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-role-btn"
                                                            data-role-id="{{ $role->id }}"
                                                            data-role-name="{{ $role->role_name ?? $role->name }}">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Permissions Tab -->
                    <div class="tab-pane fade" id="permissions" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">All Permissions</h6>
                            <button class="btn btn-sm btn-primary-custom" id="addPermissionBtn">
                                <i class="bi bi-plus-circle"></i> Add New Permission
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="permissionsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Permission Name</th>
                                        <th>Guard</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($permissions) && $permissions->count() > 0)
                                        @foreach($permissions as $permission)
                                            <tr>
                                                <td><strong><code>{{ $permission->name }}</code></strong></td>
                                                <td><span class="badge bg-secondary">{{ $permission->guard_name ?? 'web' }}</span></td>
                                                <td>
                                                    @if(isset($permission->created_at))
                                                        @if(is_string($permission->created_at))
                                                            {{ \Carbon\Carbon::parse($permission->created_at)->format('d M Y') }}
                                                        @else
                                                            {{ $permission->created_at->format('d M Y') }}
                                                        @endif
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="3" class="text-center py-4">
                                                <i class="bi bi-inbox" style="font-size: 48px; color: #940000;"></i>
                                                <p class="text-muted mt-3 mb-0">No permissions found. Click "Add New Permission" to create one.</p>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Add Role Modal --}}
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addRoleModalLabel">
                    <i class="bi bi-person-badge"></i> Add New Role
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addRoleForm">
                @csrf
                <input type="hidden" name="schoolID" value="{{ $schoolID ?? '' }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="role_name" class="form-control" placeholder="e.g., Academic, Headmaster, Librarian" required>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold mb-0">Assign Permissions <span class="text-danger">*</span></label>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary" id="selectAllPermissions">
                                        <i class="bi bi-check-all"></i> Select All
                                    </button>
                                <button type="button" class="btn btn-outline-secondary" id="deselectAllPermissions">
                                    <i class="bi bi-x-square"></i> Deselect All
                                    </button>
                                </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-search"></i> Search Permission Category
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="searchPermissionCategory"
                                   placeholder="Search by category name (e.g., Examination, Class, Timetable...)">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Type to filter categories. Categories: Examination, Classes, Subject, Result, Attendance, Student, Parent, Timetable, Fees, Accommodation, Library, Calendar, Fingerprint, Task, SMS
                            </small>
                        </div>
                        <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;" id="permissionsContainer">
                            @php
                                // New permission structure: Each category has 4 actions: create, update, delete, read_only
                                $permissionCategories = [
                                    'Examination Management' => 'examination',
                                    'Classes Management' => 'classes',
                                    'Subject Management' => 'subject',
                                    'Result Management' => 'result',
                                    'Attendance Management' => 'attendance',
                                    'Student Management' => 'student',
                                    'Parent Management' => 'parent',
                                    'Timetable Management' => 'timetable',
                                    'Fees Management' => 'fees',
                                    'Accommodation Management' => 'accommodation',
                                    'Library Management' => 'library',
                                    'Calendar Management' => 'calendar',
                                    'Fingerprint Settings' => 'fingerprint',
                                    'Task Management' => 'task',
                                    'SMS Information' => 'sms',
                                ];
                                $permissionActions = ['create', 'update', 'delete', 'read_only'];
                            @endphp

                            @if(count($permissionCategories) > 0)
                                @foreach($permissionCategories as $categoryName => $categoryKey)
                                    <div class="mb-4 permission-category-group" data-category-name="{{ strtolower($categoryKey) }}">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="text-primary-custom fw-bold mb-0">
                                                <i class="bi bi-folder-fill"></i> {{ $loop->iteration }}. {{ $categoryName }}
                                            </h6>
                                            <button type="button" class="btn btn-sm btn-outline-primary category-select-all" data-category="{{ $loop->iteration }}">
                                                <i class="bi bi-check-square"></i> Select All
                                            </button>
                                        </div>
                                        <div class="row ms-4">
                                            @foreach($permissionActions as $action)
                                                @php
                                                    $permissionName = $categoryKey . '_' . $action;
                                                    $actionLabel = ucfirst(str_replace('_', ' ', $action));
                                                @endphp
                                                <div class="col-md-6 col-lg-3 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" value="{{ $permissionName }}" id="perm_{{ md5($permissionName) }}" data-category="{{ $loop->parent->iteration }}">
                                                        <label class="form-check-label" for="perm_{{ md5($permissionName) }}">
                                                            <code class="text-dark" style="font-size: 0.85rem;">{{ $actionLabel }}</code>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @if(!$loop->last)
                                        <hr class="my-3 category-separator">
                                    @endif
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox" style="font-size: 48px; color: #940000;"></i>
                                    <p class="text-muted mt-3 mb-0">No permissions available.</p>
                                </div>
                            @endif
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-info-circle"></i> Select one or more permissions for this role. Example: Role "Academic" can have permissions like <code>add_subject</code>, <code>approve_exams</code>, <code>approve_results</code>
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Create Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Permission Modal --}}
<div class="modal fade" id="addPermissionModal" tabindex="-1" aria-labelledby="addPermissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addPermissionModalLabel">
                    <i class="bi bi-key"></i> Add New Permission(s)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPermissionForm">
                @csrf
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" id="permissionTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="single-tab" data-bs-toggle="tab" data-bs-target="#singlePermission" type="button" role="tab">
                                <i class="bi bi-plus-circle"></i> Single Permission
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulkPermissions" type="button" role="tab">
                                <i class="bi bi-list-ul"></i> Bulk Create (Multiple)
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="permissionTabsContent">
                        <!-- Single Permission Tab -->
                        <div class="tab-pane fade show active" id="singlePermission" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Permission Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="singlePermissionName" class="form-control" placeholder="e.g., create_examination, approve_results" required>
                                <small class="text-muted">Use lowercase with underscores (e.g., create_examination, approve_results)</small>
                            </div>
                        </div>

                        <!-- Bulk Permissions Tab -->
                        <div class="tab-pane fade" id="bulkPermissions" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Permissions (One per line) <span class="text-danger">*</span></label>
                                <textarea name="permissions_text" id="bulkPermissionsText" class="form-control" rows="10" placeholder="Enter one permission per line, e.g:&#10;create_timetable_category&#10;edit_timetable_category&#10;create_class&#10;edit_class&#10;create_examination&#10;approve_results&#10;view_exam_papers&#10;approve_exam_paper&#10;reject_exam_paper"></textarea>
                                <small class="text-muted">Enter one permission name per line. Duplicates will be skipped.</small>
                            </div>
                            <div class="alert alert-info">
                                <strong><i class="bi bi-info-circle"></i> Quick Add:</strong>
                                <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="quickAddPermissions">
                                    <i class="bi bi-lightning"></i> Add All Default Permissions
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Create Permission(s)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Role Name Modal --}}
<div class="modal fade" id="editRoleNameModal" tabindex="-1" aria-labelledby="editRoleNameModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editRoleNameModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Role Name
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editRoleNameForm">
                @csrf
                <input type="hidden" name="role_id" id="edit_role_name_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Role Name <span class="text-danger">*</span></label>
                        <input type="text"
                               name="role_name"
                               id="edit_role_name_input"
                               class="form-control"
                               placeholder="e.g., Academic, Headmaster, Librarian"
                               required>
                        <small class="text-muted">Enter a new name for this role.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Update Role Name
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Role Permissions Modal --}}
<div class="modal fade" id="editRolePermissionsModal" tabindex="-1" aria-labelledby="editRolePermissionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editRolePermissionsModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Role Permissions
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editRolePermissionsForm">
                @csrf
                <input type="hidden" name="role_id" id="edit_role_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Role</label>
                        <input type="text" class="form-control" id="edit_role_name" readonly style="background-color: #e9ecef;">
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold mb-0">Select Permissions</label>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary" id="selectAllEditPermissions" style="display: none;">
                                    <i class="bi bi-check-all"></i> Select All
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="deselectAllEditPermissions" style="display: none;">
                                    <i class="bi bi-x-square"></i> Deselect All
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-search"></i> Search Permission Category
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="searchEditPermissionCategory"
                                   placeholder="Search by category name (e.g., Examination, Class, Timetable...)">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Type to filter categories. Categories: Examination, Classes, Subject, Result, Attendance, Student, Parent, Timetable, Fees, Accommodation, Library, Calendar, Fingerprint, Task, SMS
                            </small>
                        </div>
                        <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;" id="editPermissionsContainer">
                            <div class="text-center">
                                <div class="spinner-border text-primary-custom" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> You can select multiple permissions for this role.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Update Permissions
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- jQuery - Load first -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- Bootstrap 5 JS bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoYz1FZC/t6mxzq5iMZ4Btr1aQz3xz6tztJ8rZfW2a4R2p"
        crossorigin="anonymous"></script>

{{-- Teacher Attendance Modal --}}
<div class="modal fade" id="teacherAttendanceModal" tabindex="-1" aria-labelledby="teacherAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down modal-dialog-centered modal-dialog-scrollable" style="max-width: 95%; width: 95%;">
        <div class="modal-content" style="border-radius: 0;">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="teacherAttendanceModalLabel">
                    <i class="bi bi-calendar-check"></i> Teacher Fingerprint Attendance
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs" id="teacherAttendanceTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="teacher-fingerprint-attendance-tab" data-bs-toggle="tab" href="#teacher-fingerprint-attendance" role="tab" aria-controls="teacher-fingerprint-attendance" aria-selected="true">
                            <i class="bi bi-fingerprint"></i> Fingerprint Attendance
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="teacher-fingerprint-attendance-overview-tab" data-bs-toggle="tab" href="#teacher-fingerprint-attendance-overview" role="tab" aria-controls="teacher-fingerprint-attendance-overview" aria-selected="false">
                            <i class="bi bi-bar-chart-fill"></i> Fingerprint Attendance Overview
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="all-attendance-tab" data-bs-toggle="tab" href="#all-attendance" role="tab" aria-controls="all-attendance" aria-selected="false">
                            <i class="bi bi-list-ul"></i> All Attendance
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content mt-3" id="teacherAttendanceTabContent">
                    <!-- Fingerprint Attendance Tab -->
                    <div class="tab-pane fade show active" id="teacher-fingerprint-attendance" role="tabpanel" aria-labelledby="teacher-fingerprint-attendance-tab">
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-fingerprint"></i> Fingerprint Attendance from Biometric System
                            </h6>
                            <div class="d-flex align-items-center gap-2">
                                <div>
                                    <label class="form-label mb-1" style="font-size: 0.85rem;">Filter by Date</label>
                                    <input type="date" class="form-control form-control-sm" id="teacherFingerprintAttendanceDateFilter" style="width: 180px;">
                                </div>
                                <button type="button" class="btn btn-sm btn-primary-custom mt-3" id="refreshTeacherFingerprintAttendance">
                                    <i class="bi bi-arrow-repeat"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <div id="teacherFingerprintAttendanceContent">
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> Click <strong>Refresh</strong> to load attendance records from the biometric system.
                            </div>
                        </div>
                    </div>

                    <!-- Fingerprint Attendance Overview Tab -->
                    <div class="tab-pane fade" id="teacher-fingerprint-attendance-overview" role="tabpanel" aria-labelledby="teacher-fingerprint-attendance-overview-tab">
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="teacherFingerprintOverviewSearchType">Search Type</label>
                                        <select class="form-control" id="teacherFingerprintOverviewSearchType">
                                            <option value="day">By Day</option>
                                            <option value="month" selected>By Month</option>
                                            <option value="year">By Year</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2" id="monthPickerContainer">
                                    <div class="form-group">
                                        <label for="teacherFingerprintOverviewMonth">Select Month</label>
                                        <input type="month" class="form-control" id="teacherFingerprintOverviewMonth" value="{{ date('Y-m') }}">
                                    </div>
                                </div>
                                <div class="col-md-2" id="yearPickerContainer" style="display: none;">
                                    <div class="form-group">
                                        <label for="teacherFingerprintOverviewYear">Select Year</label>
                                        <input type="number" class="form-control" id="teacherFingerprintOverviewYear" min="2020" max="2099" value="{{ date('Y') }}">
                                    </div>
                                </div>
                                <div class="col-md-2" id="dayPickerContainer" style="display: none;">
                                    <div class="form-group">
                                        <label for="teacherFingerprintOverviewSearchDate">Select Date</label>
                                        <input type="date" class="form-control" id="teacherFingerprintOverviewSearchDate" value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="button" class="btn btn-primary-custom btn-block" id="generateTeacherFingerprintOverviewBtn">
                                                <i class="bi bi-search"></i> Generate Overview
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div class="btn-group" role="group" style="width: 100%;">
                                            <button type="button" class="btn btn-success btn-sm" id="exportTeacherAttendanceExcelBtn" title="Export to Excel" style="display: inline-block;">
                                                <i class="bi bi-file-earmark-excel"></i> Excel
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" id="exportTeacherAttendancePdfBtn" title="Export to PDF" style="display: inline-block;">
                                                <i class="bi bi-file-earmark-pdf"></i> PDF
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="teacherFingerprintAttendanceOverviewContent">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Please select search type and date to generate fingerprint attendance overview.
                            </div>
                        </div>

                        <!-- Charts Container -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary-custom text-white">
                                        <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Fingerprint Attendance Chart</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="teacherFingerprintAttendanceChart" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary-custom text-white">
                                        <h6 class="mb-0"><i class="bi bi-pie-chart"></i> Check In/Out Distribution</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="teacherFingerprintStatusChart" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- All Attendance Tab -->
                    <div class="tab-pane fade" id="all-attendance" role="tabpanel" aria-labelledby="all-attendance-tab">
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-list-ul"></i> All Attendance Records from Device
                            </h6>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-primary-custom" id="refreshAllAttendance">
                                    <i class="bi bi-arrow-repeat"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <div id="allAttendanceContent">
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> Click <strong>Refresh</strong> to load all attendance records from the device.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- DataTables JS --}}
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>

{{-- SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
{{-- SheetJS for Excel export --}}
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
{{-- jsPDF for PDF export --}}
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.5.31/dist/jspdf.plugin.autotable.min.js"></script>

@include('includes.footer')

<script>
    $(document).ready(function() {
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

        // Initialize modals
        var addTeacherModal = null;
        var assignRoleModal = null;
        var editTeacherModal = null;
        var changeRoleModal = null;
        var viewTeachersRolesModal = null;
        var manageRolesModal = null;
        var addRoleModal = null;
        var addPermissionModal = null;
        var editRolePermissionsModal = null;
        var rolesTable = null;
        var teacherAttendanceModal = null;

        // Universal modal close handler - ensures all close buttons work
        $(document).on('click', '[data-bs-dismiss="modal"]', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $modal = $(this).closest('.modal');
            if ($modal.length) {
                if (typeof bootstrap !== 'undefined') {
                    var modalInstance = bootstrap.Modal.getInstance($modal[0]);
                    if (modalInstance) {
                        modalInstance.hide();
                    } else {
                        var newModal = new bootstrap.Modal($modal[0]);
                        newModal.hide();
                    }
                } else if ($.fn.modal) {
                    $modal.modal('hide');
                }
            }
        });

        // Also handle btn-close buttons specifically
        $(document).on('click', '.btn-close', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $modal = $(this).closest('.modal');
            if ($modal.length) {
                if (typeof bootstrap !== 'undefined') {
                    var modalInstance = bootstrap.Modal.getInstance($modal[0]);
                    if (modalInstance) {
                        modalInstance.hide();
                    } else {
                        var newModal = new bootstrap.Modal($modal[0]);
                        newModal.hide();
                    }
                } else if ($.fn.modal) {
                    $modal.modal('hide');
                }
            }
        });

        if (typeof bootstrap !== 'undefined') {
            if (document.getElementById('addTeacherModal')) {
                addTeacherModal = new bootstrap.Modal(document.getElementById('addTeacherModal'));
            }
            if (document.getElementById('assignRoleModal')) {
                assignRoleModal = new bootstrap.Modal(document.getElementById('assignRoleModal'));
            }
            if (document.getElementById('editTeacherModal')) {
                editTeacherModal = new bootstrap.Modal(document.getElementById('editTeacherModal'));
            }
            if (document.getElementById('changeRoleModal')) {
                changeRoleModal = new bootstrap.Modal(document.getElementById('changeRoleModal'));
            }
            if (document.getElementById('viewTeachersRolesModal')) {
                viewTeachersRolesModal = new bootstrap.Modal(document.getElementById('viewTeachersRolesModal'));
            }
            if (document.getElementById('manageRolesModal')) {
                manageRolesModal = new bootstrap.Modal(document.getElementById('manageRolesModal'));
            }
            if (document.getElementById('addRoleModal')) {
                addRoleModal = new bootstrap.Modal(document.getElementById('addRoleModal'));
            }
            if (document.getElementById('addPermissionModal')) {
                addPermissionModal = new bootstrap.Modal(document.getElementById('addPermissionModal'));
            }
            if (document.getElementById('editRoleNameModal')) {
                editRoleNameModal = new bootstrap.Modal(document.getElementById('editRoleNameModal'));
            }
            if (document.getElementById('editRolePermissionsModal')) {
                editRolePermissionsModal = new bootstrap.Modal(document.getElementById('editRolePermissionsModal'));
            }
            if (document.getElementById('teacherAttendanceModal')) {
                teacherAttendanceModal = new bootstrap.Modal(document.getElementById('teacherAttendanceModal'));
            }
        }

        // Handle Add Teacher Button Click
        $(document).on('click', '#addTeacherBtn', function(e) {
            e.preventDefault();
            if (!hasPermission('register_teacher')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You are not allowed to perform this action. You need the register_teacher permission.'
                });
                return false;
            }
            if (typeof bootstrap !== 'undefined' && addTeacherModal) {
                addTeacherModal.show();
            } else if ($('#addTeacherModal').length) {
                $('#addTeacherModal').modal('show');
            }
            // Ensure submit button is visible
            $('#teacherForm').find('button[type="submit"]').show().css('display', 'inline-block');
            $('#teacherForm').find('.modal-footer').show();
            return false;
        });

        // Handle View Teacher Attendance Button Click
        $(document).on('click', '#viewTeacherAttendanceBtn', function(e) {
            e.preventDefault();
            if (typeof bootstrap !== 'undefined' && teacherAttendanceModal) {
                teacherAttendanceModal.show();
            } else if ($('#teacherAttendanceModal').length) {
                $('#teacherAttendanceModal').modal('show');
            }
            // Initialize tabs when modal is shown
            setTimeout(function() {
                // Ensure first tab is active
                $('a#teacher-fingerprint-attendance-tab').tab('show');
            }, 300);
            return false;
        });

        // Handle View Teachers Roles Button Click
        $(document).on('click', '#viewTeachersRolesBtn', function(e) {
            e.preventDefault();
            if (!hasPermission('view_teachers_roles')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You are not allowed to perform this action. You need the view_teachers_roles permission.'
                });
                return false;
            }

            // Destroy existing DataTable if it exists
            if (rolesTable) {
                rolesTable.destroy();
                rolesTable = null;
            }

            // Show modal first
            if (typeof bootstrap !== 'undefined' && viewTeachersRolesModal) {
                viewTeachersRolesModal.show();
            } else {
                $('#viewTeachersRolesModal').modal('show');
            }

            // Initialize DataTable after modal is shown
            setTimeout(function() {
                if ($('#teachersRolesTable tbody tr').length > 0) {
                    rolesTable = $('#teachersRolesTable').DataTable({
                        "order": [[1, "asc"]], // Sort by name
                        "pageLength": 25,
                        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                        "autoWidth": false, // Let CSS control width
                        "responsive": false, // Disable responsive mode to allow horizontal scroll
                        "language": {
                            "search": "Search:",
                            "lengthMenu": "Show _MENU_ records per page",
                            "info": "Showing _START_ to _END_ of _TOTAL_ records",
                            "infoEmpty": "No records available",
                            "infoFiltered": "(filtered from _MAX_ total records)",
                            "zeroRecords": "No matching records found"
                        },
                        "columnDefs": [
                            { "orderable": false, "targets": [0, 5] } // Disable sorting on Image and Actions columns
                        ]
                    });
                } else {
                    rolesTable = $('#teachersRolesTable').DataTable({
                        "order": [[1, "asc"]], // Sort by name
                        "pageLength": 25,
                        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                        "autoWidth": false, // Let CSS control width
                        "responsive": false, // Disable responsive mode to allow horizontal scroll
                        "language": {
                            "search": "Search:",
                            "lengthMenu": "Show _MENU_ records per page",
                            "info": "Showing _START_ to _END_ of _TOTAL_ records",
                            "infoEmpty": "No records available",
                            "infoFiltered": "(filtered from _MAX_ total records)",
                            "zeroRecords": "No matching records found",
                            "emptyTable": "<div class='text-center py-5'><i class='bi bi-inbox' style='font-size: 48px; color: #940000;'></i><p class='mt-3 mb-0 text-muted'>No teachers with assigned roles found.</p></div>"
                        },
                        "columnDefs": [
                            { "orderable": false, "targets": [0, 5] } // Disable sorting on Image and Actions columns
                        ]
                    });
                }
            }, 300);

            return false;
        });

        // Handle Assign Role Button Click
        $(document).on('click', '#assignRoleBtn', function(e) {
            e.preventDefault();
            if (typeof bootstrap !== 'undefined' && assignRoleModal) {
                assignRoleModal.show();
            } else if ($('#assignRoleModal').length) {
                $('#assignRoleModal').modal('show');
            }
            return false;
        });

        // Initialize DataTable (like manage_library style)
        if ($('#teachersTable tbody tr').length > 0) {
            var table = $('#teachersTable').DataTable({
                "order": [[1, "asc"]], // Sort by name
                "pageLength": 25,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "autoWidth": false, // Let CSS control width
                "responsive": false, // Disable responsive mode to allow horizontal scroll
                "responsive": true,
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "order": [[1, "asc"]],
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ teachers",
                    "infoEmpty": "No teachers found",
                    "infoFiltered": "(filtered from _MAX_ total teachers)",
                    "zeroRecords": "No matching teachers found"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [0, 6] }
                ]
            });
        } else {
            var table = $('#teachersTable').DataTable({
                "responsive": true,
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ teachers",
                    "infoEmpty": "No teachers found",
                    "infoFiltered": "(filtered from _MAX_ total teachers)",
                    "zeroRecords": "No matching teachers found",
                    "emptyTable": "<div class='text-center py-5'><i class='bi bi-inbox' style='font-size: 48px; color: #940000;'></i><p class='mt-3 mb-0 text-muted'>No teachers found. Click 'Add New Teacher' to get started.</p></div>"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [0, 6] }
                ]
            });
        }


        // Handle View Teacher Button Click
        $(document).on('click', '.view-teacher-btn', function(e) {
            e.preventDefault();
            if (!hasPermission('view_teacher')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You are not allowed to perform this action. You need the view_teacher permission.'
                });
                return false;
            }
            var teacherId = $(this).data('teacher-id');
            var $details = $('.teacher-full-details[data-teacher-id="' + teacherId + '"]').html();

            if ($details) {
                $('#teacherDetailsContent').html($details);

                if (typeof bootstrap !== 'undefined') {
                    var viewModal = new bootstrap.Modal(document.getElementById('viewTeacherModal'));
                    viewModal.show();
                } else {
                    $('#viewTeacherModal').modal('show');
                }
            }
        });

        // Handle Edit Teacher Button Click
        $(document).on('click', '.edit-teacher-btn', function(e) {
            e.preventDefault();
            if (!hasPermission('edit_teacher')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You are not allowed to perform this action. You need the edit_teacher permission.'
                });
                return false;
            }
            var teacherId = $(this).data('teacher-id');

            // Show loading state
            if (typeof bootstrap !== 'undefined' && editTeacherModal) {
                editTeacherModal.show();
            } else {
                $('#editTeacherModal').modal('show');
            }

            // Fetch teacher data
            $.ajax({
                url: "{{ route('get_teacher', ':id') }}".replace(':id', teacherId),
                type: "GET",
                success: function(response) {
                    var teacher = response.teacher;

                    // Populate form fields
                    $('#edit_teacher_id').val(teacher.id);
                    $('#edit_first_name').val(teacher.first_name);
                    $('#edit_middle_name').val(teacher.middle_name || '');
                    $('#edit_last_name').val(teacher.last_name);
                    $('#edit_gender').val(teacher.gender);
                    $('#edit_email').val(teacher.email);
                    $('#edit_phone_number').val(teacher.phone_number);
                    $('#edit_national_id').val(teacher.national_id);
                    $('#edit_employee_number').val(teacher.employee_number);
                    $('#edit_qualification').val(teacher.qualification || '');
                    $('#edit_specialization').val(teacher.specialization || '');
                    $('#edit_experience').val(teacher.experience || '');
                    $('#edit_date_of_birth').val(teacher.date_of_birth || '');
                    $('#edit_date_hired').val(teacher.date_hired || '');
                    $('#edit_position').val(teacher.position || '');
                    $('#edit_status').val(teacher.status || 'Active');
                    $('#edit_address').val(teacher.address || '');

                    // Reset phone validation
                    $('#edit_phone_number').removeClass('is-invalid is-valid');
                    $('#edit_phone_error').hide();
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load teacher data. Please try again.'
                    });
                    if (typeof bootstrap !== 'undefined' && editTeacherModal) {
                        editTeacherModal.hide();
                    } else {
                        $('#editTeacherModal').modal('hide');
                    }
                }
            });
        });

        // Handle Send to Fingerprint Device Button Click
        $(document).on('click', '.send-to-fingerprint-btn', function(e) {
            e.preventDefault();
            var teacherId = $(this).data('teacher-id');
            var teacherName = $(this).data('teacher-name');
            var $btn = $(this);
            var originalHtml = $btn.html();

            Swal.fire({
                title: 'Send to Fingerprint Device?',
                html: 'Are you sure you want to send <strong>' + teacherName + '</strong> to the fingerprint device?<br><br><small class="text-muted">This will generate a unique fingerprint ID and register the teacher to the biometric device.</small>',
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
                        url: "{{ route('send_teacher_to_fingerprint') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            teacher_id: teacherId
                        },
                        success: function(response) {
                            return response;
                        },
                        error: function(xhr) {
                            var errorMsg = 'Failed to send teacher to fingerprint device.';
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
                            html: 'Teacher <strong>' + teacherName + '</strong> has been successfully sent to the fingerprint device.<br><br><small class="text-muted">Fingerprint ID: <strong>' + (response.fingerprint_id || 'N/A') + '</strong></small>',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Reload the page to show updated data
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Partial Success',
                            html: response.message || 'Teacher was processed but there may have been issues with the fingerprint device.',
                            confirmButtonText: 'OK'
                        });
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                } else {
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        });

        // Phone Number Validation - Real-time
        $('#phone_number').on('input', function() {
            var phoneValue = $(this).val();
            var phoneRegex = /^255\d{9}$/;
            var $input = $(this);
            var $errorDiv = $('#phone_error');

            phoneValue = phoneValue.replace(/\D/g, '');
            $(this).val(phoneValue);

            if (phoneValue.length === 0) {
                $input.removeClass('is-invalid is-valid');
                $errorDiv.hide();
                return;
            }

            if (phoneRegex.test(phoneValue)) {
                $input.removeClass('is-invalid');
                $input.addClass('is-valid');
                $errorDiv.hide();
            } else {
                $input.removeClass('is-valid');
                $input.addClass('is-invalid');
                $errorDiv.show();

                if (!phoneValue.startsWith('255')) {
                    $errorDiv.text('Phone number must start with 255');
                } else if (phoneValue.length < 12) {
                    var remaining = 12 - phoneValue.length;
                    $errorDiv.text('Phone number must have 12 digits. Add ' + remaining + ' more digit(s).');
                } else if (phoneValue.length > 12) {
                    $errorDiv.text('Phone number cannot exceed 12 digits');
                } else {
                    $errorDiv.text('Phone number must have 12 digits: start with 255 followed by 9 digits');
                }
            }
        });

        // Phone Number Validation on Blur
        $('#phone_number').on('blur', function() {
            var phoneValue = $(this).val();
            var phoneRegex = /^255\d{9}$/;
            var $input = $(this);
            var $errorDiv = $('#phone_error');

            if (phoneValue.length > 0 && !phoneRegex.test(phoneValue)) {
                $input.addClass('is-invalid');
                $errorDiv.show();
                if (!phoneValue.startsWith('255')) {
                    $errorDiv.text('Phone number must start with 255');
                } else if (phoneValue.length !== 12) {
                    $errorDiv.text('Phone number must have exactly 12 digits (255 + 9 digits)');
                }
            }
        });

        // Phone Number Validation for Edit Form - Real-time
        $('#edit_phone_number').on('input', function() {
            var phoneValue = $(this).val();
            var phoneRegex = /^255\d{9}$/;
            var $input = $(this);
            var $errorDiv = $('#edit_phone_error');

            phoneValue = phoneValue.replace(/\D/g, '');
            $(this).val(phoneValue);

            if (phoneValue.length === 0) {
                $input.removeClass('is-invalid is-valid');
                $errorDiv.hide();
                return;
            }

            if (phoneRegex.test(phoneValue)) {
                $input.removeClass('is-invalid');
                $input.addClass('is-valid');
                $errorDiv.hide();
            } else {
                $input.removeClass('is-valid');
                $input.addClass('is-invalid');
                $errorDiv.show();

                if (!phoneValue.startsWith('255')) {
                    $errorDiv.text('Phone number must start with 255');
                } else if (phoneValue.length < 12) {
                    var remaining = 12 - phoneValue.length;
                    $errorDiv.text('Phone number must have 12 digits. Add ' + remaining + ' more digit(s).');
                } else if (phoneValue.length > 12) {
                    $errorDiv.text('Phone number cannot exceed 12 digits');
                } else {
                    $errorDiv.text('Phone number must have 12 digits: start with 255 followed by 9 digits');
                }
            }
        });

        // Phone Number Validation for Edit Form on Blur
        $('#edit_phone_number').on('blur', function() {
            var phoneValue = $(this).val();
            var phoneRegex = /^255\d{9}$/;
            var $input = $(this);
            var $errorDiv = $('#edit_phone_error');

            if (phoneValue.length > 0 && !phoneRegex.test(phoneValue)) {
                $input.addClass('is-invalid');
                $errorDiv.show();
                if (!phoneValue.startsWith('255')) {
                    $errorDiv.text('Phone number must start with 255');
                } else if (phoneValue.length !== 12) {
                    $errorDiv.text('Phone number must have exactly 12 digits (255 + 9 digits)');
                }
            }
        });

        // Handle Change Role Button Click
        $(document).on('click', '.change-role-btn', function(e) {
            e.preventDefault();
            if (!hasPermission('assign_role_teacher')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You are not allowed to perform this action. You need the assign_role_teacher permission.'
                });
                return false;
            }
            var roleUserId = $(this).data('role-user-id');
            var roleId = $(this).data('role-id');
            var roleName = $(this).data('role-name');
            var currentTeacherId = $(this).data('current-teacher-id');
            var currentTeacherName = $(this).data('current-teacher-name');

            // Populate form
            $('#change_role_user_id').val(roleUserId);
            $('#change_role_id').val(roleId);
            $('#change_current_role_name').text(roleName);
            $('#change_current_teacher_name').text(currentTeacherName);
            $('#change_new_teacher_select').val('');

            // Remove current teacher from dropdown options
            $('#change_new_teacher_select option').show();
            $('#change_new_teacher_select option[value="' + currentTeacherId + '"]').hide();

            // Show modal
            if (typeof bootstrap !== 'undefined' && changeRoleModal) {
                changeRoleModal.show();
            } else {
                $('#changeRoleModal').modal('show');
            }
        });

        // Handle Change Role Form Submission
        $(document).on('submit', '#changeRoleForm', function(e) {
            e.preventDefault();

            var formData = {
                role_user_id: $('#change_role_user_id').val(),
                new_teacher_id: $('#change_new_teacher_select').val(),
                _token: $('input[name="_token"]').val()
            };

            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Changing...');

            $.ajax({
                url: "{{ route('change_teacher_role') }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    if (typeof bootstrap !== 'undefined' && changeRoleModal) {
                        changeRoleModal.hide();
                    } else {
                        $('#changeRoleModal').modal('hide');
                    }

                    $('#changeRoleForm')[0].reset();
                    $submitBtn.prop('disabled', false).html(originalText);

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.success || 'Role changed successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
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
                            text: errorList
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Something went wrong. Please try again.');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg
                        });
                    }
                }
            });
        });

        // Handle Remove Role Button Click
        $(document).on('click', '.remove-role-btn', function(e) {
            e.preventDefault();
            if (!hasPermission('assign_role_teacher')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You are not allowed to perform this action. You need the assign_role_teacher permission.'
                });
                return false;
            }
            var roleUserId = $(this).data('role-user-id');
            var roleId = $(this).data('role-id');
            var roleName = $(this).data('role-name');
            var teacherId = $(this).data('teacher-id');
            var teacherName = $(this).data('teacher-name');

            Swal.fire({
                title: 'Remove Role?',
                html: 'Are you sure you want to remove the role <strong>"' + roleName + '"</strong> from <strong>' + teacherName + '</strong>?<br><br><small class="text-muted">This will unassign the role from the teacher. The role itself will not be deleted.</small>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<i class="bi bi-x-circle"></i> Yes, Remove',
                cancelButtonText: '<i class="bi bi-x-circle"></i> Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Removing...',
                        text: 'Please wait while we remove the role.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Remove role via AJAX
                    $.ajax({
                        url: "{{ route('remove_teacher_role', ':id') }}".replace(':id', roleUserId),
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Removed!',
                                text: response.success || 'Role removed successfully!',
                                timer: 3000,
                                showConfirmButton: true
                            }).then(function() {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            var errorMessage = 'An error occurred while removing the role.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: errorMessage
                            });
                        }
                    });
                }
            });
        });

        // Handle Assign Role Form Submission
        $(document).on('submit', '#assignRoleForm', function(e) {
            e.preventDefault();
            if (!hasPermission('assign_role_teacher')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You are not allowed to perform this action. You need the assign_role_teacher permission.'
                });
                return false;
            }

            var formData = {
                teacher_id: $('#teacher_select').val(),
                role_id: $('#role_select').val(),
                _token: $('input[name="_token"]').val()
            };

            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Assigning...');

            $.ajax({
                url: "{{ route('save_teacher_role') }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    if (typeof bootstrap !== 'undefined' && assignRoleModal) {
                        assignRoleModal.hide();
                    } else {
                        $('#assignRoleModal').modal('hide');
                    }

                    $('#assignRoleForm')[0].reset();
                    $submitBtn.prop('disabled', false).html(originalText);

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.success || 'Role assigned successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
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
                            text: errorList
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Something went wrong. Please try again.');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg
                        });
                    }
                }
            });
        });

        // Handle Teacher Form Submission
        $(document).on('submit', '#teacherForm', function(e) {
            e.preventDefault();
            if (!hasPermission('register_teacher')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You are not allowed to perform this action. You need the register_teacher permission.'
                });
                return false;
            }
            e.stopPropagation();

            let formData = new FormData(this);

            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

            $.ajax({
                url: "{{ route('save_teachers') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (typeof bootstrap !== 'undefined' && addTeacherModal) {
                        addTeacherModal.hide();
                    } else {
                        $('#addTeacherModal').modal('hide');
                    }

                    $('#teacherForm')[0].reset();
                    $submitBtn.prop('disabled', false).html(originalText);

                    Swal.fire({
                        title: 'Teacher Registered Successfully!',
                        html: '<div class="text-center">' +
                              '<p class="mb-3">Teacher registered successfully</p>' +
                              '<p class="mb-0">Please continue register user in fingerprint device ID <strong style="font-size: 1.2rem; color: #940000;">' + (response.fingerprint_id || 'N/A') + '</strong></p>' +
                              '</div>',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#940000',
                        width: '500px'
                    }).then(function() {
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
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
                            text: errorList
                        });
                    } else if (xhr.status === 500) {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : 'Server error occurred. Please check console for details.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: errorMsg
                        });
                    } else if (xhr.status === 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Unable to connect to server. Please check your internet connection.'
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Something went wrong. Please try again.');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg
                        });
                    }
                }
            });

            return false;
        });

        // Handle Edit Teacher Form Submission
        $(document).on('submit', '#editTeacherForm', function(e) {
            e.preventDefault();
            if (!hasPermission('edit_teacher')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You are not allowed to perform this action. You need the edit_teacher permission.'
                });
                return false;
            }
            e.stopPropagation();

            let formData = new FormData(this);

            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Updating...');

            $.ajax({
                url: "{{ route('update_teacher') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (typeof bootstrap !== 'undefined' && editTeacherModal) {
                        editTeacherModal.hide();
                    } else {
                        $('#editTeacherModal').modal('hide');
                    }

                    $('#editTeacherForm')[0].reset();
                    $submitBtn.prop('disabled', false).html(originalText);

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.success || 'Teacher updated successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
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
                            text: errorList
                        });
                    } else if (xhr.status === 500) {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : 'Server error occurred. Please check console for details.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: errorMsg
                        });
                    } else if (xhr.status === 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Unable to connect to server. Please check your internet connection.'
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Something went wrong. Please try again.');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg
                        });
                    }
                }
            });

            return false;
        });

        // Reset forms when modals are closed
        if (document.getElementById('addTeacherModal')) {
            document.getElementById('addTeacherModal').addEventListener('hidden.bs.modal', function() {
            $('#teacherForm')[0].reset();
                $('#phone_number').removeClass('is-invalid is-valid');
                $('#phone_error').hide();
            });
            // Ensure submit button is visible when modal is shown
            document.getElementById('addTeacherModal').addEventListener('shown.bs.modal', function() {
                $('#teacherForm').find('button[type="submit"]').show().css('display', 'inline-block');
                $('#teacherForm').find('.modal-footer').show();
            });
        }

        if (document.getElementById('assignRoleModal')) {
            document.getElementById('assignRoleModal').addEventListener('hidden.bs.modal', function() {
                $('#assignRoleForm')[0].reset();
            });
        }

        if (document.getElementById('editTeacherModal')) {
            document.getElementById('editTeacherModal').addEventListener('hidden.bs.modal', function() {
                $('#editTeacherForm')[0].reset();
                $('#edit_phone_number').removeClass('is-invalid is-valid');
                $('#edit_phone_error').hide();
            });
        }

        if (document.getElementById('changeRoleModal')) {
            document.getElementById('changeRoleModal').addEventListener('hidden.bs.modal', function() {
                $('#changeRoleForm')[0].reset();
                // Show all options again
                $('#change_new_teacher_select option').show();
            });
        }

        if (document.getElementById('viewTeachersRolesModal')) {
            document.getElementById('viewTeachersRolesModal').addEventListener('hidden.bs.modal', function() {
                // Destroy DataTable when modal is closed
                if (rolesTable) {
                    rolesTable.destroy();
                    rolesTable = null;
                }
            });
        }

        // Handle Manage Roles & Permissions Button Click
        $(document).on('click', '#manageRolesBtn', function(e) {
            e.preventDefault();
            if (!hasPermission('manage_roles_permissions')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You are not allowed to perform this action. You need the manage_roles_permissions permission.'
                });
                return false;
            }
            if (typeof bootstrap !== 'undefined' && manageRolesModal) {
                manageRolesModal.show();
            } else if ($('#manageRolesModal').length) {
                $('#manageRolesModal').modal('show');
            }
            return false;
        });

        // Handle Add Role Button Click
        $(document).on('click', '#addRoleBtn', function(e) {
            e.preventDefault();
            if (!hasPermission('create_roles')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You are not allowed to perform this action. You need the create_roles permission.'
                });
                return false;
            }
            if (typeof bootstrap !== 'undefined' && addRoleModal) {
                addRoleModal.show();
            } else if ($('#addRoleModal').length) {
                $('#addRoleModal').modal('show');
            }
            return false;
        });

        // Handle Add Permission Button Click
        $(document).on('click', '#addPermissionBtn', function(e) {
            e.preventDefault();
            if (!hasPermission('create_permission')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You are not allowed to perform this action. You need the create_permission permission.'
                });
                return false;
            }
            if (typeof bootstrap !== 'undefined' && addPermissionModal) {
                addPermissionModal.show();
            } else if ($('#addPermissionModal').length) {
                $('#addPermissionModal').modal('show');
            }
            return false;
        });

        // Handle Add Role Form Submission
        $(document).on('submit', '#addRoleForm', function(e) {
            e.preventDefault();
            if (!hasPermission('create_roles')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You are not allowed to perform this action. You need the create_roles permission.'
                });
                return false;
            }

            var roleName = $('input[name="role_name"]', '#addRoleForm').val().trim();
            if (!roleName) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please enter a role name.'
                });
                return;
            }

            var formData = {
                role_name: roleName,
                permissions: [],
                _token: $('input[name="_token"]', '#addRoleForm').val()
            };

            // Collect selected permissions (now using permission names, not IDs)
            $('input[name="permissions[]"]:checked', '#addRoleForm').each(function() {
                formData.permissions.push($(this).val());
            });

            // Validate at least one permission is selected
            if (formData.permissions.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select at least one permission for this role. Example: For "Academic" role, you can select permissions like add_subject, approve_exams, approve_results'
                });
                return;
            }

            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Creating...');

            $.ajax({
                url: "{{ route('create_role') }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    if (typeof bootstrap !== 'undefined' && addRoleModal) {
                        addRoleModal.hide();
                    } else {
                        $('#addRoleModal').modal('hide');
                    }
                    $('#addRoleForm')[0].reset();
                    $('.permission-checkbox').prop('checked', false);
                    $('#selectAllPermissions').html('<i class="bi bi-check-all"></i> Select All');
                    $submitBtn.prop('disabled', false).html(originalText);

                    var message = response.success || 'Role created successfully!';
                    if (response.permissions && response.permissions.length > 0) {
                        message += '\n\nPermissions assigned:\n' + response.permissions.join(', ');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: message,
                        timer: 4000,
                        showConfirmButton: true
                    }).then(function() {
                        location.reload();
                    });
                },
                error: function(xhr) {
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
                            text: errorList
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : 'Something went wrong. Please try again.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg
                        });
                    }
                }
            });
        });

        // Quick Add Default Permissions
        $(document).on('click', '#quickAddPermissions', function(e) {
            e.preventDefault();
            var defaultPermissions = [
                // Timetable Management
                'create_timetable_category',
                'edit_timetable_category',
                'delete_timetable_category',
                'show_timetable_category',
                'approve_timetable_category',
                'create_timetable',
                'edit_timetable',
                'show_timetable',
                'view_all_timetable',
                'review_timetable',
                'approval_timetable',
                // Class Management
                'create_class_category',
                'edit_class_category',
                'delete_class_category',
                'show_class_category',
                'view_all_class_category',
                'approval_class_category',
                'create_class',
                'edit_class',
                'delete_class',
                'show_class',
                'view_all_class',
                'review_class',
                'approval_class',
                // Examination Management
                'create_examination',
                'edit_exam',
                'delete_exam',
                'view_exam_details',
                'approve_exam',
                'reject_exam',
                'view_exam_papers',
                'approve_exam_paper',
                'reject_exam_paper',
                'toggle_enter_result',
                'toggle_publish_result',
                'toggle_upload_paper',
                'view_exam_results',
                'update_results_status',
                // Subject Management
                'create_subject',
                'update_subject',
                'delete_subject',
                'approve_created_subject',
                // Manage Teachers
                'register_teacher',
                'delete_teacher',
                'edit_teacher',
                'assign_role_teacher',
                'approve_registered_teacher',
                'create_roles',
                'assign_permission',
                // Other
                'register_parents',
                'register_teachers',
                'register_students'
            ];
            $('#bulkPermissionsText').val(defaultPermissions.join('\n'));
        });

        // Handle Add Permission Form Submission
        $(document).on('submit', '#addPermissionForm', function(e) {
            e.preventDefault();
            if (!hasPermission('create_permission')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You are not allowed to perform this action. You need the create_permission permission.'
                });
                return false;
            }

            // Check which tab is active
            var activeTab = $('#permissionTabs .nav-link.active').attr('id');
            var formData = {
                _token: $('input[name="_token"]', '#addPermissionForm').val()
            };

            // Handle single permission
            if (activeTab === 'single-tab') {
                var permissionName = $('#singlePermissionName').val().trim();
                if (!permissionName) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please enter a permission name.'
                    });
                    return;
                }
                formData.name = permissionName;
            }
            // Handle bulk permissions
            else if (activeTab === 'bulk-tab') {
                var permissionsText = $('#bulkPermissionsText').val().trim();
                if (!permissionsText) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please enter at least one permission.'
                    });
                    return;
                }

                // Split by newline and filter empty lines
                var permissions = permissionsText.split('\n')
                    .map(function(p) { return p.trim(); })
                    .filter(function(p) { return p.length > 0; });

                if (permissions.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please enter at least one valid permission.'
                    });
                    return;
                }

                formData.permissions = permissions;
            }

            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Creating...');

            $.ajax({
                url: "{{ route('create_permission') }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    if (typeof bootstrap !== 'undefined' && addPermissionModal) {
                        addPermissionModal.hide();
                    } else {
                        $('#addPermissionModal').modal('hide');
                    }
                    $('#addPermissionForm')[0].reset();
                    $submitBtn.prop('disabled', false).html(originalText);

                    var message = response.success || 'Permission(s) created successfully!';
                    if (response.skipped && response.skipped.length > 0) {
                        message += '\n\nSkipped (already exist): ' + response.skipped.join(', ');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: message,
                        timer: 3000,
                        showConfirmButton: true
                    }).then(function() {
                        location.reload();
                    });
                },
                error: function(xhr) {
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
                            text: errorList
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : 'Something went wrong. Please try again.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg
                        });
                    }
                }
            });
        });

        // Handle Edit Role Name Button Click
        $(document).on('click', '.edit-role-name-btn', function(e) {
            e.preventDefault();
            if (!hasPermission('update_role')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You are not allowed to perform this action. You need the update_role permission.'
                });
                return false;
            }
            var roleId = $(this).data('role-id');
            var roleName = $(this).data('role-name');

            $('#edit_role_name_id').val(roleId);
            $('#edit_role_name_input').val(roleName);

            if (typeof bootstrap !== 'undefined' && editRoleNameModal) {
                editRoleNameModal.show();
            } else if ($('#editRoleNameModal').length) {
                $('#editRoleNameModal').modal('show');
            }
        });

        // Handle Edit Role Name Form Submission
        $(document).on('submit', '#editRoleNameForm', function(e) {
            e.preventDefault();
            if (!hasPermission('update_role')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You are not allowed to perform this action. You need the update_role permission.'
                });
                return false;
            }

            var formData = {
                role_id: $('#edit_role_name_id').val(),
                role_name: $('#edit_role_name_input').val().trim(),
                _token: $('input[name="_token"]', '#editRoleNameForm').val()
            };

            if (!formData.role_name) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please enter a role name.'
                });
                return;
            }

            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Updating...');

            $.ajax({
                url: "{{ route('update_role') }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    if (typeof bootstrap !== 'undefined' && editRoleNameModal) {
                        editRoleNameModal.hide();
                    } else {
                        $('#editRoleNameModal').modal('hide');
                    }
                    $submitBtn.prop('disabled', false).html(originalText);

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.success || 'Role name updated successfully!',
                        timer: 3000,
                        showConfirmButton: true
                    }).then(function() {
                        location.reload();
                    });
                },
                error: function(xhr) {
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
                            text: errorList
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : 'Something went wrong. Please try again.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg
                        });
                    }
                }
            });
        });

        // Handle Delete Role Button Click
        $(document).on('click', '.delete-role-btn', function(e) {
            e.preventDefault();
            if (!hasPermission('delete_role')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You are not allowed to perform this action. You need the delete_role permission.'
                });
                return false;
            }
            var roleId = $(this).data('role-id');
            var roleName = $(this).data('role-name');

            Swal.fire({
                title: 'Delete Role?',
                html: 'Are you sure you want to delete the role <strong>"' + roleName + '"</strong>?<br><br><small class="text-muted">This action cannot be undone. All permissions associated with this role will also be deleted.</small>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<i class="bi bi-trash"></i> Yes, Delete',
                cancelButtonText: '<i class="bi bi-x-circle"></i> Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait while we delete the role.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Delete role via AJAX
                    $.ajax({
                        url: "{{ route('delete_role', ':id') }}".replace(':id', roleId),
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.success || 'Role deleted successfully!',
                                timer: 3000,
                                showConfirmButton: true
                            }).then(function() {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            var errorMessage = 'An error occurred while deleting the role.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: errorMessage
                            });
                        }
                    });
                }
            });
        });

        $(document).on('click', '.edit-role-permissions-btn', function(e) {
            e.preventDefault();
            if (!hasPermission('assign_permission')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You are not allowed to perform this action. You need the assign_permission permission.'
                });
                return false;
            }
            var roleId = $(this).data('role-id');
            var roleName = $(this).data('role-name');

            $('#edit_role_id').val(roleId);
            $('#edit_role_name').val(roleName);
            $('#editPermissionsContainer').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"></div></div>');

            if (typeof bootstrap !== 'undefined' && editRolePermissionsModal) {
                editRolePermissionsModal.show();
            } else if ($('#editRolePermissionsModal').length) {
                $('#editRolePermissionsModal').modal('show');
            }

            // Load role with permissions
            $.ajax({
                url: "{{ route('get_role_with_permissions', ':id') }}".replace(':id', roleId),
                type: "GET",
                success: function(response) {
                    if (response.success && response.role) {
                        var role = response.role;

                        // New permission structure: Each category has 4 actions: create, update, delete, read_only
                        var permissionCategories = {
                            'Examination Management': 'examination',
                            'Classes Management': 'classes',
                            'Subject Management': 'subject',
                            'Result Management': 'result',
                            'Attendance Management': 'attendance',
                            'Student Management': 'student',
                            'Parent Management': 'parent',
                            'Timetable Management': 'timetable',
                            'Fees Management': 'fees',
                            'Accommodation Management': 'accommodation',
                            'Library Management': 'library',
                            'Calendar Management': 'calendar',
                            'Fingerprint Settings': 'fingerprint',
                            'Task Management': 'task',
                            'SMS Information': 'sms',
                        };
                        var permissionActions = ['create', 'update', 'delete', 'read_only'];
                        var actionLabels = {
                            'create': 'Create',
                            'update': 'Update',
                            'delete': 'Delete',
                            'read_only': 'Read Only'
                        };

                        var rolePermissionNames = role.permissions ? role.permissions.map(p => p.name) : [];
                        var html = '';
                        var categoryIndex = 0;

                        $.each(permissionCategories, function(categoryName, categoryKey) {
                            categoryIndex++;
                            html += '<div class="mb-4 edit-permission-category-group" data-category-name="' + categoryKey.toLowerCase() + '">';
                            html += '<div class="d-flex justify-content-between align-items-center mb-3">';
                            html += '<h6 class="text-primary-custom fw-bold mb-0">';
                            html += '<i class="bi bi-folder-fill"></i> ' + categoryIndex + '. ' + categoryName;
                            html += '</h6>';
                            html += '<button type="button" class="btn btn-sm btn-outline-primary edit-category-select-all" data-category="' + categoryIndex + '">';
                            html += '<i class="bi bi-check-square"></i> Select All';
                            html += '</button>';
                            html += '</div>';
                            html += '<div class="row ms-4">';

                            $.each(permissionActions, function(index, action) {
                                var permissionName = categoryKey + '_' + action;
                                var isChecked = rolePermissionNames.includes(permissionName) ? 'checked' : '';
                                var permId = btoa(permissionName).replace(/[^a-zA-Z0-9]/g, '');
                                html += '<div class="col-md-6 col-lg-3 mb-2">';
                                html += '<div class="form-check">';
                                html += '<input class="form-check-input edit-permission-checkbox" type="checkbox" name="permissions[]" value="' + permissionName + '" id="edit_perm_' + permId + '" data-category="' + categoryIndex + '" ' + isChecked + '>';
                                html += '<label class="form-check-label" for="edit_perm_' + permId + '">';
                                html += '<code class="text-dark" style="font-size: 0.85rem;">' + actionLabels[action] + '</code>';
                                html += '</label>';
                                html += '</div>';
                                html += '</div>';
                            });

                            html += '</div>';
                            html += '</div>';
                            if (categoryIndex < Object.keys(permissionCategories).length) {
                                html += '<hr class="my-3 edit-category-separator">';
                            }
                        });

                        $('#editPermissionsContainer').html(html);
                        $('#selectAllEditPermissions').show();
                        $('#deselectAllEditPermissions').show();

                        // Update Select All button state
                        updateEditSelectAllButton();
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load role data.'
                    });
                }
            });
        });

        // Handle Edit Role Permissions Form Submission
        $(document).on('submit', '#editRolePermissionsForm', function(e) {
            e.preventDefault();
            if (!hasPermission('assign_permission')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You are not allowed to perform this action. You need the assign_permission permission.'
                });
                return false;
            }

            var formData = {
                role_id: $('#edit_role_id').val(),
                permissions: [],
                _token: $('input[name="_token"]', '#editRolePermissionsForm').val()
            };

            // Collect selected permissions
            $('input[name="permissions[]"]:checked', '#editRolePermissionsForm').each(function() {
                formData.permissions.push($(this).val());
            });

            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Updating...');

            $.ajax({
                url: "{{ route('update_role_permissions') }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    if (typeof bootstrap !== 'undefined' && editRolePermissionsModal) {
                        editRolePermissionsModal.hide();
                    } else {
                        $('#editRolePermissionsModal').modal('hide');
                    }
                    $submitBtn.prop('disabled', false).html(originalText);

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.success || 'Role permissions updated successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload();
                    });
                },
                error: function(xhr) {
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
                            text: errorList
                        });
                    } else {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : 'Something went wrong. Please try again.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg
                        });
                    }
                }
            });
        });

        // Select All / Deselect All Permissions Handler (Add Role Modal) - Toggle behavior
        $(document).on('click', '#selectAllPermissions', function(e) {
            e.preventDefault();
            var $checkboxes = $('.permission-checkbox', '#permissionsContainer');
            var allChecked = $checkboxes.length > 0 && $checkboxes.length === $checkboxes.filter(':checked').length;

            if (allChecked) {
                // Deselect all
                $checkboxes.prop('checked', false);
                $(this).html('<i class="bi bi-check-all"></i> Select All');
                $('.category-select-all').html('<i class="bi bi-check-square"></i> Select Category');
            } else {
                // Select all
            $checkboxes.prop('checked', true);
            $(this).html('<i class="bi bi-x-square"></i> Deselect All');
                $('.category-select-all').html('<i class="bi bi-x-square"></i> Deselect Category');
            }
        });

        // Deselect All Permissions Handler (Add Role Modal)
        $(document).on('click', '#deselectAllPermissions', function(e) {
            e.preventDefault();
            var $checkboxes = $('.permission-checkbox', '#permissionsContainer');
            $checkboxes.prop('checked', false);
            // Update category buttons
            $('.category-select-all').html('<i class="bi bi-check-square"></i> Select Category');
        });

        // Select/Deselect Category Handler (Add Role Modal)
        $(document).on('click', '.category-select-all', function(e) {
            e.preventDefault();
            var category = $(this).data('category');
            var $checkboxes = $('.permission-checkbox[data-category="' + category + '"]', '#permissionsContainer');
            var allChecked = $checkboxes.length > 0 && $checkboxes.length === $checkboxes.filter(':checked').length;

            if (allChecked) {
                $checkboxes.prop('checked', false);
                $(this).html('<i class="bi bi-check-square"></i> Select Category');
            } else {
                $checkboxes.prop('checked', true);
                $(this).html('<i class="bi bi-x-square"></i> Deselect Category');
            }

            // Update main select all button
            updateSelectAllButton();
        });

        // Search Permission Category Handler
        $(document).on('input', '#searchPermissionCategory', function(e) {
            var searchTerm = $(this).val().toLowerCase().trim();
            var $categoryGroups = $('.permission-category-group');
            var $separators = $('.category-separator');
            var visibleCount = 0;

            if (searchTerm === '') {
                // Show all categories if search is empty
                $categoryGroups.show();
                $separators.show();
            } else {
                // Filter categories based on search term
                $categoryGroups.each(function() {
                    var $group = $(this);
                    var categoryName = $group.data('category-name') || '';

                    if (categoryName.includes(searchTerm)) {
                        $group.show();
                        visibleCount++;
                    } else {
                        $group.hide();
                    }
                });

                // Hide separators between hidden categories
                $separators.each(function() {
                    var $separator = $(this);
                    var $prevGroup = $separator.prev('.permission-category-group');
                    var $nextGroup = $separator.next('.permission-category-group');

                    if ($prevGroup.length && $nextGroup.length) {
                        if ($prevGroup.is(':hidden') || $nextGroup.is(':hidden')) {
                            $separator.hide();
                        } else {
                            $separator.show();
                        }
                    } else {
                        $separator.hide();
                    }
                });

                // Show message if no categories found
                if (visibleCount === 0) {
                    if ($('#noCategoryFound').length === 0) {
                        $('#permissionsContainer').append(
                            '<div id="noCategoryFound" class="text-center py-4">' +
                            '<i class="bi bi-search" style="font-size: 48px; color: #940000;"></i>' +
                            '<p class="text-muted mt-3 mb-0">No category found matching "' + searchTerm + '"</p>' +
                            '<small class="text-muted">Try: Timetable, Class, Examination, Subject, Manage Teachers, or Other</small>' +
                            '</div>'
                        );
                    }
                } else {
                    $('#noCategoryFound').remove();
                }
            }
        });

        // Clear search when modal is closed
        $('#addRoleModal').on('hidden.bs.modal', function() {
            $('#searchPermissionCategory').val('');
            $('.permission-category-group').show();
            $('.category-separator').show();
            $('#noCategoryFound').remove();
        });

        // Select All Permissions Handler (Edit Role Modal)
        $(document).on('click', '#selectAllEditPermissions', function(e) {
            e.preventDefault();
            var $checkboxes = $('.edit-permission-checkbox', '#editPermissionsContainer');
            var allChecked = $checkboxes.length > 0 && $checkboxes.length === $checkboxes.filter(':checked').length;

            if (allChecked) {
                $checkboxes.prop('checked', false);
                $(this).html('<i class="bi bi-check-all"></i> Select All');
                $('.edit-category-select-all').html('<i class="bi bi-check-square"></i> Select Category');
            } else {
                $checkboxes.prop('checked', true);
                $(this).html('<i class="bi bi-x-square"></i> Deselect All');
                $('.edit-category-select-all').html('<i class="bi bi-x-square"></i> Deselect Category');
            }
            updateEditSelectAllButton();
        });

        // Deselect All Permissions Handler (Edit Role Modal)
        $(document).on('click', '#deselectAllEditPermissions', function(e) {
            e.preventDefault();
            var $checkboxes = $('.edit-permission-checkbox', '#editPermissionsContainer');
            $checkboxes.prop('checked', false);
            $('#selectAllEditPermissions').html('<i class="bi bi-check-all"></i> Select All');
            $('.edit-category-select-all').html('<i class="bi bi-check-square"></i> Select Category');
            updateEditSelectAllButton();
        });

        // Select/Deselect Category Handler (Edit Role Modal)
        $(document).on('click', '.edit-category-select-all', function(e) {
            e.preventDefault();
            var category = $(this).data('category');
            var $checkboxes = $('.edit-permission-checkbox[data-category="' + category + '"]', '#editPermissionsContainer');
            var allChecked = $checkboxes.length > 0 && $checkboxes.length === $checkboxes.filter(':checked').length;

            if (allChecked) {
                $checkboxes.prop('checked', false);
                $(this).html('<i class="bi bi-check-square"></i> Select Category');
            } else {
                $checkboxes.prop('checked', true);
                $(this).html('<i class="bi bi-x-square"></i> Deselect Category');
            }
            updateEditSelectAllButton();
        });

        // Search Permission Category Handler (Edit Role Modal)
        $(document).on('input', '#searchEditPermissionCategory', function(e) {
            var searchTerm = $(this).val().toLowerCase().trim();
            var $categoryGroups = $('.edit-permission-category-group');
            var $separators = $('.edit-category-separator');
            var visibleCount = 0;

            if (searchTerm === '') {
                $categoryGroups.show();
                $separators.show();
            } else {
                $categoryGroups.each(function() {
                    var $group = $(this);
                    var categoryName = $group.data('category-name') || '';

                    if (categoryName.includes(searchTerm)) {
                        $group.show();
                        visibleCount++;
                    } else {
                        $group.hide();
                    }
                });

                $separators.each(function() {
                    var $separator = $(this);
                    var $prevGroup = $separator.prev('.edit-permission-category-group');
                    var $nextGroup = $separator.next('.edit-permission-category-group');

                    if ($prevGroup.length && $nextGroup.length) {
                        if ($prevGroup.is(':hidden') || $nextGroup.is(':hidden')) {
                            $separator.hide();
                        } else {
                            $separator.show();
                        }
                    } else {
                        $separator.hide();
                    }
                });

                if (visibleCount === 0) {
                    if ($('#noEditCategoryFound').length === 0) {
                        $('#editPermissionsContainer').append(
                            '<div id="noEditCategoryFound" class="text-center py-4">' +
                            '<i class="bi bi-search" style="font-size: 48px; color: #940000;"></i>' +
                            '<p class="text-muted mt-3 mb-0">No category found matching "' + searchTerm + '"</p>' +
                            '<small class="text-muted">Try: Timetable, Class, Examination, Subject, Manage Teachers, or Other</small>' +
                            '</div>'
                        );
                    }
                } else {
                    $('#noEditCategoryFound').remove();
                }
            }
        });

        // Function to update Edit Select All button state
        function updateEditSelectAllButton() {
            var $checkboxes = $('.edit-permission-checkbox', '#editPermissionsContainer');
            var $selectAllBtn = $('#selectAllEditPermissions');
            if ($checkboxes.length > 0) {
                var checkedCount = $checkboxes.filter(':checked').length;
                var allChecked = $checkboxes.length === checkedCount;

                if (allChecked) {
                    $selectAllBtn.html('<i class="bi bi-x-square"></i> Deselect All');
                } else {
                    $selectAllBtn.html('<i class="bi bi-check-all"></i> Select All');
                }

                // Update category buttons
                $('.edit-category-select-all').each(function() {
                    var category = $(this).data('category');
                    var $catCheckboxes = $('.edit-permission-checkbox[data-category="' + category + '"]', '#editPermissionsContainer');
                    var catAllChecked = $catCheckboxes.length > 0 && $catCheckboxes.length === $catCheckboxes.filter(':checked').length;

                    if (catAllChecked) {
                        $(this).html('<i class="bi bi-x-square"></i> Deselect Category');
                    } else {
                        $(this).html('<i class="bi bi-check-square"></i> Select Category');
                    }
                });
            }
        }

        // Update button state when checkbox changes (Edit Role Modal)
        $(document).on('change', '.edit-permission-checkbox', function() {
            updateEditSelectAllButton();
        });

        // Function to update Select All button state
        function updateSelectAllButton() {
            var $checkboxes = $('.permission-checkbox', '#permissionsContainer');
            var $selectAllBtn = $('#selectAllPermissions');
            if ($checkboxes.length > 0) {
                var checkedCount = $checkboxes.filter(':checked').length;
                var allChecked = $checkboxes.length === checkedCount;

                if (allChecked) {
                    $selectAllBtn.html('<i class="bi bi-x-square"></i> Deselect All');
                } else {
                    $selectAllBtn.html('<i class="bi bi-check-all"></i> Select All');
                }
            }

            // Update category buttons
            $('.category-select-all').each(function() {
                var category = $(this).data('category');
                var $catCheckboxes = $('.permission-checkbox[data-category="' + category + '"]', '#permissionsContainer');
                var catAllChecked = $catCheckboxes.length > 0 && $catCheckboxes.length === $catCheckboxes.filter(':checked').length;

                if (catAllChecked) {
                    $(this).html('<i class="bi bi-x-square"></i> Deselect Category');
                } else {
                    $(this).html('<i class="bi bi-check-square"></i> Select Category');
                }
            });
        }

        // Update Select All button text when checkboxes change (Add Role Modal)
        $(document).on('change', '.permission-checkbox', function() {
            updateSelectAllButton();
        });


        // Reset forms when modals are closed
        $('#addRoleModal').on('hidden.bs.modal', function() {
            $('#addRoleForm')[0].reset();
            $('.permission-checkbox').prop('checked', false);
            $('#selectAllPermissions').html('<i class="bi bi-check-all"></i> Select All');
            $('.category-select-all').html('<i class="bi bi-check-square"></i> Select Category');
        });

        $('#addPermissionModal').on('hidden.bs.modal', function() {
            $('#addPermissionForm')[0].reset();
            // Reset to single tab
            $('#single-tab').tab('show');
        });

        $('#editRoleNameModal').on('hidden.bs.modal', function() {
            $('#editRoleNameForm')[0].reset();
        });

        $('#editRolePermissionsModal').on('hidden.bs.modal', function() {
            $('#editRolePermissionsForm')[0].reset();
            $('#searchEditPermissionCategory').val('');
            $('.edit-permission-category-group').show();
            $('.edit-category-separator').show();
            $('#noEditCategoryFound').remove();
        });

        // ==================== TEACHER FINGERPRINT ATTENDANCE ====================
        // When Teacher Fingerprint Attendance tab is shown, load data
        $('a#teacher-fingerprint-attendance-tab').on('shown.bs.tab', function () {
            loadTeacherFingerprintAttendance();
        });

        // When Teacher Fingerprint Attendance Overview tab is shown
        $('a#teacher-fingerprint-attendance-overview-tab').on('shown.bs.tab', function (e) {
            // Tab is now visible
        });

        // Manual click handlers for both tabs to ensure they work properly
        $(document).on('click', '#teacherAttendanceTabs a.nav-link', function(e) {
            var $target = $(this);
            var targetId = $target.attr('href');
            
            // Remove active from all tabs
            $('#teacherAttendanceTabs a.nav-link').removeClass('active').attr('aria-selected', 'false');
            // Add active to clicked tab
            $target.addClass('active').attr('aria-selected', 'true');
            
            // Hide all tab panes
            $('#teacherAttendanceTabContent .tab-pane').removeClass('show active');
            // Show target tab pane
            $(targetId).addClass('show active');
            
            // If it's the first tab, load data
            if (targetId === '#teacher-fingerprint-attendance') {
                loadTeacherFingerprintAttendance();
            }
        });

        // Refresh button inside fingerprint tab
        $('#refreshTeacherFingerprintAttendance').on('click', function() {
            loadTeacherFingerprintAttendance();
        });

        // Filter by date change
        $('#teacherFingerprintAttendanceDateFilter').on('change', function() {
            loadTeacherFingerprintAttendance();
        });

        // Load Teacher Fingerprint Attendance
        function loadTeacherFingerprintAttendance(page = 1) {
            const container = $('#teacherFingerprintAttendanceContent');
            const dateFilter = $('#teacherFingerprintAttendanceDateFilter').val();

            container.html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 mb-0">Syncing from external API...</p>
                </div>
            `);

            // First try to sync from external API (this will save/update records to local database)
            $.ajax({
                url: '{{ url("api/attendance/all-teachers") }}',
                type: 'GET',
                data: { 
                    page: page
                },
                dataType: 'json',
                timeout: 15000, // 15 seconds timeout
                success: function(syncResponse) {
                    // After successful sync, load from local database
                    loadTeacherFingerprintAttendanceFromLocal(page, dateFilter);
                },
                error: function(xhr, status, error) {
                    console.log('API sync failed, loading from local database:', error);
                    // If API fails, load from local database
                    loadTeacherFingerprintAttendanceFromLocal(page, dateFilter, true);
                }
            });
        }

        // Load Teacher Fingerprint Attendance from Local Database
        function loadTeacherFingerprintAttendanceFromLocal(page, dateFilter, apiFailed = false) {
            const container = $('#teacherFingerprintAttendanceContent');

            container.html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 mb-0">${apiFailed ? 'Loading from local database (API unavailable)...' : 'Loading attendance records from local database...'}</p>
                </div>
            `);

            $.ajax({
                url: '{{ url("api/attendance/teachers-fingerprint") }}',
                type: 'GET',
                data: { 
                    page: page,
                    date: dateFilter
                },
                dataType: 'json',
                success: function(data) {
                    if (!data.success) {
                        container.html(`
                            <div class="alert alert-danger mb-0">
                                <i class="bi bi-exclamation-triangle"></i> ${data.message || 'Failed to load teacher attendance records.'}
                            </div>
                        `);
                        return;
                    }

                    let records = data.data || [];
                    const pagination = data.pagination || null;

                    if (records.length === 0) {
                        const message = 'No teacher attendance records found' + (dateFilter ? ' for the selected date' : '') + '.';
                        container.html(`
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> ${message}
                            </div>
                        `);
                        return;
                    }

                    // Function to format datetime to time only (HH:mm:ss)
                    function formatTimeOnly(datetime) {
                        if (!datetime) return '';
                        const parts = datetime.split(' ');
                        if (parts.length === 2) {
                            return parts[1]; // Return time part only
                        }
                        return datetime;
                    }

                    // Calculate statistics for widget
                    const totalRecords = records.length;
                    const presentCount = records.filter(r => r.check_in_time).length;
                    const absentCount = totalRecords - presentCount;
                    const checkedOutCount = records.filter(r => r.check_out_time).length;
                    const today = new Date().toISOString().split('T')[0];
                    const todayRecords = records.filter(r => r.attendance_date === today).length;

                    // Attendance Widget
                    let html = `
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h3 class="mb-0" style="color: #ffffff; font-weight: bold;">${totalRecords}</h3>
                                        <p class="mb-0 mt-2" style="color: #ffffff;">Total Records</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h3 class="mb-0" style="color: #ffffff; font-weight: bold;">${presentCount}</h3>
                                        <p class="mb-0 mt-2" style="color: #ffffff;">Present</p>
                                        <small style="color: #ffffff;">Checked In</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-danger text-white">
                                    <div class="card-body text-center">
                                        <h3 class="mb-0" style="color: #ffffff; font-weight: bold;">${absentCount}</h3>
                                        <p class="mb-0 mt-2" style="color: #ffffff;">Absent</p>
                                        <small style="color: #ffffff;">No Check In</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h3 class="mb-0" style="color: #ffffff; font-weight: bold;">${checkedOutCount}</h3>
                                        <p class="mb-0 mt-2" style="color: #ffffff;">Checked Out</p>
                                        <small style="color: #ffffff;">Today: ${todayRecords}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm" id="teacherFingerprintAttendanceTable">
                                <thead class="bg-primary-custom text-white">
                                    <tr>
                                        <th>#</th>
                                        <th>Teacher Name</th>
                                        <th>Position</th>
                                        <th>Employee Number</th>
                                        <th>Fingerprint ID</th>
                                        <th>Attendance Date</th>
                                        <th>Check In Time</th>
                                        <th>Check Out Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    $.each(records, function(index, rec) {
                        const teacherInfo = rec.teacher_info || {};
                        const userData = rec.user || {};
                        const fullName = teacherInfo.full_name || userData.name || 'N/A';
                        const position = teacherInfo.position || 'N/A';
                        const employeeNumber = teacherInfo.employee_number || 'N/A';
                        const fingerprintId = userData.enroll_id || 'N/A';
                        const attendanceDate = rec.attendance_date || '';
                        
                        // Format times to HH:mm:ss only
                        const checkInTime = formatTimeOnly(rec.check_in_time || '');
                        const checkOutTime = formatTimeOnly(rec.check_out_time || '');

                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td><strong>${fullName}</strong></td>
                                <td>${position}</td>
                                <td>${employeeNumber}</td>
                                <td>${fingerprintId}</td>
                                <td>${attendanceDate}</td>
                                <td>${checkInTime ? '<span class="badge bg-success text-white">' + checkInTime + '</span>' : '<span class="text-muted">-</span>'}</td>
                                <td>${checkOutTime ? '<span class="badge bg-primary text-white">' + checkOutTime + '</span>' : '<span class="text-muted">-</span>'}</td>
                            </tr>
                        `;
                    });

                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;

                    container.html(html);

                    // Initialize DataTable
                    if ($.fn.DataTable.isDataTable('#teacherFingerprintAttendanceTable')) {
                        $('#teacherFingerprintAttendanceTable').DataTable().destroy();
                    }
                    
                    $('#teacherFingerprintAttendanceTable').DataTable({
                        order: [[5, 'desc']], // Sort by attendance date descending
                        pageLength: 25,
                        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                        language: {
                            search: "Search:",
                            lengthMenu: "Show _MENU_ records per page",
                            info: "Showing _START_ to _END_ of _TOTAL_ records",
                            infoEmpty: "No records available",
                            infoFiltered: "(filtered from _MAX_ total records)",
                            paginate: {
                                first: "First",
                                last: "Last",
                                next: "Next",
                                previous: "Previous"
                            }
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error loading teacher attendance:', error);
                    container.html(`
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-exclamation-triangle"></i> Failed to load teacher attendance records. Please try again.
                            <br><small>Error: ${error}</small>
                        </div>
                    `);
                }
            });
        }

        // Load Teacher Fingerprint Attendance from Local Database
        function loadTeacherFingerprintAttendanceFromLocal(page, dateFilter, apiFailed = false) {
            const container = $('#teacherFingerprintAttendanceContent');

            container.html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 mb-0">${apiFailed ? 'Loading from local database (API unavailable)...' : 'Loading attendance records from local database...'}</p>
                </div>
            `);

            $.ajax({
                url: '{{ url("api/attendance/teachers-fingerprint") }}',
                type: 'GET',
                data: { 
                    page: page,
                    date: dateFilter
                },
                dataType: 'json',
                success: function(data) {
                    if (!data.success) {
                        container.html(`
                            <div class="alert alert-danger mb-0">
                                <i class="bi bi-exclamation-triangle"></i> ${data.message || 'Failed to load teacher attendance.'}
                            </div>
                        `);
                        return;
                    }

                    let records = data.data || [];
                    const pagination = data.pagination || null;

                    if (records.length === 0) {
                        const message = 'No teacher attendance records found' + (dateFilter ? ' for the selected date' : '') + '.';
                        container.html(`
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> ${message}
                            </div>
                        `);
                        return;
                    }

                    // Function to format datetime to time only (HH:mm:ss)
                    function formatTimeOnly(datetime) {
                        if (!datetime) return '';
                        const parts = datetime.split(' ');
                        if (parts.length === 2) {
                            return parts[1]; // Return time part only
                        }
                        return datetime;
                    }

                    // Calculate statistics for widget
                    const totalRecords = records.length;
                    const presentCount = records.filter(r => r.check_in_time).length;
                    const absentCount = totalRecords - presentCount;
                    const checkedOutCount = records.filter(r => r.check_out_time).length;
                    const today = new Date().toISOString().split('T')[0];
                    const todayRecords = records.filter(r => r.attendance_date === today).length;

                    // Attendance Widget
                    let html = `
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h3 class="mb-0" style="color: #ffffff; font-weight: bold;">${totalRecords}</h3>
                                        <p class="mb-0 mt-2" style="color: #ffffff;">Total Records</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h3 class="mb-0" style="color: #ffffff; font-weight: bold;">${presentCount}</h3>
                                        <p class="mb-0 mt-2" style="color: #ffffff;">Present</p>
                                        <small style="color: #ffffff;">Checked In</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-danger text-white">
                                    <div class="card-body text-center">
                                        <h3 class="mb-0" style="color: #ffffff; font-weight: bold;">${absentCount}</h3>
                                        <p class="mb-0 mt-2" style="color: #ffffff;">Absent</p>
                                        <small style="color: #ffffff;">No Check In</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h3 class="mb-0" style="color: #ffffff; font-weight: bold;">${checkedOutCount}</h3>
                                        <p class="mb-0 mt-2" style="color: #ffffff;">Checked Out</p>
                                        <small style="color: #ffffff;">Today: ${todayRecords}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm" id="teacherAttendanceTable">
                                <thead class="bg-primary-custom text-white">
                                    <tr>
                                        <th>#</th>
                                        <th>Teacher Name</th>
                                        <th>Position</th>
                                        <th>Fingerprint ID</th>
                                        <th>Attendance Date</th>
                                        <th>Check In Time</th>
                                        <th>Check Out Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    $.each(records, function(index, rec) {
                        const teacherInfo = rec.teacher_info || {};
                        const fullName = teacherInfo.full_name || (rec.user && rec.user.name) || 'N/A';
                        const position = teacherInfo.position || 'N/A';
                        const fingerprintId = (rec.user && rec.user.enroll_id) || teacherInfo.teacherID || 'N/A';
                        const attendanceDate = rec.attendance_date || '';
                        
                        // Format times to HH:mm:ss only
                        const checkInTime = formatTimeOnly(rec.check_in_time || '');
                        const checkOutTime = formatTimeOnly(rec.check_out_time || '');

                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td><strong>${fullName}</strong></td>
                                <td>${position}</td>
                                <td>${fingerprintId}</td>
                                <td>${attendanceDate}</td>
                                <td>${checkInTime ? '<span class="badge bg-success text-white">' + checkInTime + '</span>' : '<span class="text-muted">-</span>'}</td>
                                <td>${checkOutTime ? '<span class="badge bg-primary text-white">' + checkOutTime + '</span>' : '<span class="text-muted">-</span>'}</td>
                            </tr>
                        `;
                    });

                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;

                    // Simple pagination footer
                    if (pagination) {
                        html += `
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted">
                                    Page ${pagination.current_page} of ${pagination.last_page}, Total: ${pagination.total}
                                </small>
                                <div>
                        `;

                        if (pagination.current_page > 1) {
                            html += `
                                <button class="btn btn-sm btn-outline-secondary me-1" onclick="loadTeacherFingerprintAttendance(${pagination.current_page - 1})">
                                    <i class="bi bi-chevron-left"></i> Prev
                                </button>
                            `;
                        }

                        if (pagination.current_page < pagination.last_page) {
                            html += `
                                <button class="btn btn-sm btn-outline-secondary" onclick="loadTeacherFingerprintAttendance(${pagination.current_page + 1})">
                                    Next <i class="bi bi-chevron-right"></i>
                                </button>
                            `;
                        }

                        html += `
                                </div>
                            </div>
                        `;
                    }

                    container.html(html);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading teacher attendance:', error);
                    container.html(`
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-exclamation-triangle"></i> Failed to load teacher attendance records. Please try again.
                        </div>
                    `);
                }
            });
        }

        // ==================== ALL ATTENDANCE ====================
        // When All Attendance tab is shown, load data
        $('a#all-attendance-tab').on('shown.bs.tab', function () {
            loadAllAttendance();
        });

        // Refresh button for all attendance
        $('#refreshAllAttendance').on('click', function() {
            loadAllAttendance();
        });

        // Load All Attendance from API (filtered to teachers only)
        function loadAllAttendance(page = 1) {
            const container = $('#allAttendanceContent');

            container.html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 mb-0">Loading teacher attendance records from device...</p>
                </div>
            `);

            $.ajax({
                url: '{{ url("api/attendance/all-teachers") }}',
                type: 'GET',
                data: { 
                    page: page
                },
                dataType: 'json',
                timeout: 15000, // 15 seconds timeout
                success: function(data) {
                    if (!data.success) {
                        container.html(`
                            <div class="alert alert-danger mb-0">
                                <i class="bi bi-exclamation-triangle"></i> ${data.message || 'Failed to load attendance records.'}
                            </div>
                        `);
                        return;
                    }

                    let records = data.data || [];
                    const pagination = data.pagination || null;

                    if (records.length === 0) {
                        container.html(`
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> No teacher attendance records found.
                            </div>
                        `);
                        return;
                    }

                    // Function to format datetime to time only (HH:mm:ss)
                    function formatTimeOnly(datetime) {
                        if (!datetime) return '';
                        const parts = datetime.split(' ');
                        if (parts.length === 2) {
                            return parts[1]; // Return time part only
                        }
                        return datetime;
                    }

                    let html = `
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm" id="allAttendanceTable">
                                <thead class="bg-primary-custom text-white">
                                    <tr>
                                        <th>#</th>
                                        <th>Teacher Name</th>
                                        <th>Position</th>
                                        <th>Employee Number</th>
                                        <th>Enroll ID</th>
                                        <th>Attendance Date</th>
                                        <th>Check In Time</th>
                                        <th>Check Out Time</th>
                                        <th>Status</th>
                                        <th>Verify Mode</th>
                                        <th>Device IP</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    $.each(records, function(index, rec) {
                        const teacherInfo = rec.teacher_info || {};
                        const userData = rec.user || {};
                        const fullName = teacherInfo.full_name || userData.name || 'N/A';
                        const position = teacherInfo.position || 'N/A';
                        const employeeNumber = teacherInfo.employee_number || 'N/A';
                        const enrollId = userData.enroll_id || 'N/A';
                        const attendanceDate = rec.attendance_date || '';
                        
                        // Format times to HH:mm:ss only
                        const checkInTime = formatTimeOnly(rec.check_in_time || '');
                        const checkOutTime = formatTimeOnly(rec.check_out_time || '');
                        const status = rec.status === '1' ? '<span class="badge bg-success">Present</span>' : '<span class="badge bg-warning">' + (rec.status || 'N/A') + '</span>';
                        const verifyMode = rec.verify_mode || 'N/A';
                        const deviceIp = rec.device_ip || 'N/A';

                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td><strong>${fullName}</strong></td>
                                <td>${position}</td>
                                <td>${employeeNumber}</td>
                                <td>${enrollId}</td>
                                <td>${attendanceDate}</td>
                                <td>${checkInTime ? '<span class="badge bg-success text-white">' + checkInTime + '</span>' : '<span class="text-muted">-</span>'}</td>
                                <td>${checkOutTime ? '<span class="badge bg-primary text-white">' + checkOutTime + '</span>' : '<span class="text-muted">-</span>'}</td>
                                <td>${status}</td>
                                <td>${verifyMode}</td>
                                <td><small>${deviceIp}</small></td>
                            </tr>
                        `;
                    });

                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;

                    // Pagination footer
                    if (pagination) {
                        html += `
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted">
                                    Page ${pagination.current_page} of ${pagination.last_page}, Total: ${pagination.total} records
                                </small>
                                <div>
                        `;

                        if (pagination.current_page > 1) {
                            html += `
                                <button class="btn btn-sm btn-outline-secondary me-1" onclick="loadAllAttendance(${pagination.current_page - 1})">
                                    <i class="bi bi-chevron-left"></i> Prev
                                </button>
                            `;
                        }

                        if (pagination.current_page < pagination.last_page) {
                            html += `
                                <button class="btn btn-sm btn-outline-secondary" onclick="loadAllAttendance(${pagination.current_page + 1})">
                                    Next <i class="bi bi-chevron-right"></i>
                                </button>
                            `;
                        }

                        html += `</div></div>`;
                    }

                    container.html(html);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading all attendance:', error);
                    container.html(`
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-exclamation-triangle"></i> Failed to load attendance records from device. Please try again.
                            <br><small>Error: ${error}</small>
                        </div>
                    `);
                }
            });
        }

        // ==================== TEACHER FINGERPRINT ATTENDANCE OVERVIEW ====================
        // Show/hide pickers based on search type
        function toggleSearchPickers() {
            var searchType = $('#teacherFingerprintOverviewSearchType').val();
            
            // Hide all pickers first
            $('#monthPickerContainer, #yearPickerContainer, #dayPickerContainer').hide();
            
            // Show relevant picker
            if (searchType === 'month') {
                $('#monthPickerContainer').show();
                $('#exportTeacherAttendanceExcelBtn, #exportTeacherAttendancePdfBtn').show();
            } else if (searchType === 'year') {
                $('#yearPickerContainer').show();
                $('#exportTeacherAttendanceExcelBtn, #exportTeacherAttendancePdfBtn').show();
            } else {
                $('#dayPickerContainer').show();
                $('#exportTeacherAttendanceExcelBtn, #exportTeacherAttendancePdfBtn').hide();
            }
        }

        // Check on page load
        $(document).ready(function() {
            toggleSearchPickers();
        });

        // Check when search type changes
        $('#teacherFingerprintOverviewSearchType').on('change', function() {
            toggleSearchPickers();
        });

        // Generate Teacher Fingerprint Attendance Overview
        $('#generateTeacherFingerprintOverviewBtn').on('click', function() {
            var searchType = $('#teacherFingerprintOverviewSearchType').val();
            var searchDate = null;
            var searchMonth = null;
            var searchYear = null;

            if (searchType === 'month') {
                searchMonth = $('#teacherFingerprintOverviewMonth').val();
                if (!searchMonth) {
                    Swal.fire('Error', 'Please select a month', 'error');
                    return;
                }
                searchDate = searchMonth + '-01'; // First day of month
            } else if (searchType === 'year') {
                searchYear = $('#teacherFingerprintOverviewYear').val();
                if (!searchYear) {
                    Swal.fire('Error', 'Please select a year', 'error');
                    return;
                }
                searchDate = searchYear + '-01-01'; // First day of year
            } else {
                searchDate = $('#teacherFingerprintOverviewSearchDate').val();
                if (!searchDate) {
                    Swal.fire('Error', 'Please select a date', 'error');
                    return;
                }
            }

            $('#teacherFingerprintAttendanceOverviewContent').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"></div></div>');

            // Load all attendance records and process them
            loadTeacherFingerprintAttendanceOverview(searchType, searchDate, searchMonth, searchYear);
        });

        // Export to Excel (JavaScript only)
        $('#exportTeacherAttendanceExcelBtn').on('click', function() {
            if (!currentFilteredRecords || currentFilteredRecords.length === 0) {
                Swal.fire('Error', 'No data to export. Please generate overview first.', 'error');
                return;
            }
            exportTeacherAttendanceToExcel();
        });

        // Export to PDF (JavaScript only)
        $('#exportTeacherAttendancePdfBtn').on('click', function() {
            if (!currentFilteredRecords || currentFilteredRecords.length === 0) {
                Swal.fire('Error', 'No data to export. Please generate overview first.', 'error');
                return;
            }
            exportTeacherAttendanceToPdf();
        });

        // Load Teacher Fingerprint Attendance Overview
        function loadTeacherFingerprintAttendanceOverview(searchType, searchDate, searchMonth, searchYear) {
            // Show loading message
            $('#teacherFingerprintAttendanceOverviewContent').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 mb-0">Loading attendance records from device...</p>
                </div>
            `);

            // Load all records from API (filtered to teachers only)
            var allRecords = [];
            var currentPage = 1;
            var totalPages = null;

            function fetchPage(page) {
                $.ajax({
                    url: '{{ url("api/attendance/all-teachers") }}',
                    type: 'GET',
                    data: { 
                        page: page
                    },
                    dataType: 'json',
                    timeout: 15000,
                    success: function(data) {
                        if (data.success && data.data) {
                            allRecords = allRecords.concat(data.data);
                            
                            if (data.pagination && data.pagination.current_page < data.pagination.last_page) {
                                fetchPage(page + 1);
                            } else {
                                // All pages loaded, now process the data
                                // Get total teachers count from database (all teachers, not just with fingerprint_id)
                                var totalTeachers = {{ \App\Models\Teacher::count() }};
                                processTeacherFingerprintOverview(allRecords, searchType, searchDate, totalTeachers, false, searchMonth, searchYear);
                            }
                        } else {
                            $('#teacherFingerprintAttendanceOverviewContent').html(`
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle"></i> Failed to load attendance data.
                                </div>
                            `);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('API failed, trying local database:', error);
                        // If API fails, try local database
                        var totalTeachers = {{ \App\Models\Teacher::count() }};
                        loadFromLocalDatabaseForTeachers(searchType, searchDate, totalTeachers, true, searchMonth, searchYear);
                    }
                });
            }

            fetchPage(1);
        }

        // Load from Local Database for Teachers
        function loadFromLocalDatabaseForTeachers(searchType, searchDate, totalTeachers, apiFailed = false, searchMonth, searchYear) {
            var allRecords = [];
            var currentPage = 1;

            // Show loading message
            $('#teacherFingerprintAttendanceOverviewContent').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 mb-0">${apiFailed ? 'Loading from local database (API unavailable)...' : 'Loading attendance records from local database...'}</p>
                </div>
            `);

            function fetchPage(page) {
                $.ajax({
                    url: '{{ url("api/attendance/teachers-fingerprint") }}',
                    type: 'GET',
                    data: { 
                        page: page,
                        date: searchDate
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.success && data.data) {
                            allRecords = allRecords.concat(data.data);
                            
                            if (data.pagination && data.pagination.current_page < data.pagination.last_page) {
                                fetchPage(page + 1);
                            } else {
                                // All pages loaded, now process the data
                                processTeacherFingerprintOverview(allRecords, searchType, searchDate, totalTeachers, apiFailed, null, null);
                            }
                        } else {
                            const message = apiFailed 
                                ? 'No attendance data found in local database.'
                                : 'Failed to load attendance data';
                            $('#teacherFingerprintAttendanceOverviewContent').html(`<div class="alert ${apiFailed ? 'alert-warning' : 'alert-danger'}">${message}</div>`);
                        }
                    },
                    error: function(xhr) {
                        $('#teacherFingerprintAttendanceOverviewContent').html('<div class="alert alert-danger">Error loading attendance data from local database</div>');
                    }
                });
            }

            fetchPage(1);
        }

        // Process Teacher Fingerprint Attendance Overview Data
        function processTeacherFingerprintOverview(records, searchType, searchDate, totalTeachers, apiFailed = false, searchMonth, searchYear) {
            // Filter records by date based on searchType
            var filteredRecords = filterTeacherRecordsByDate(records, searchType, searchDate, searchMonth, searchYear);

            // Calculate statistics
            var stats = calculateTeacherFingerprintStats(filteredRecords, totalTeachers);

            // Store for export
            currentFilteredRecords = filteredRecords;
            currentSearchType = searchType;
            currentSearchMonth = searchMonth;
            currentSearchYear = searchYear;
            currentTotalTeachers = totalTeachers;

            // Display overview
            displayTeacherFingerprintAttendanceOverview(stats, searchType, apiFailed, filteredRecords, totalTeachers, searchMonth, searchYear);

            // Generate charts
            generateTeacherFingerprintAttendanceCharts(filteredRecords, searchType, totalTeachers);
        }

        // Filter Teacher Records by Date
        function filterTeacherRecordsByDate(records, searchType, searchDate, searchMonth, searchYear) {
            var filtered = [];
            var searchDateObj = new Date(searchDate);

            records.forEach(function(rec) {
                if (!rec.attendance_date) return;

                var recordDate = new Date(rec.attendance_date);
                var match = false;

                if (searchType === 'day') {
                    match = recordDate.toDateString() === searchDateObj.toDateString();
                } else if (searchType === 'month') {
                    if (searchMonth) {
                        var monthParts = searchMonth.split('-');
                        var monthYear = parseInt(monthParts[0]);
                        var monthMonth = parseInt(monthParts[1]) - 1; // JavaScript months are 0-indexed
                        match = recordDate.getMonth() === monthMonth && 
                                recordDate.getFullYear() === monthYear;
                    } else {
                        match = recordDate.getMonth() === searchDateObj.getMonth() && 
                                recordDate.getFullYear() === searchDateObj.getFullYear();
                    }
                } else if (searchType === 'year') {
                    var year = searchYear ? parseInt(searchYear) : searchDateObj.getFullYear();
                    match = recordDate.getFullYear() === year;
                }

                if (match) {
                    filtered.push(rec);
                }
            });

            return filtered;
        }

        // Calculate Teacher Fingerprint Statistics
        function calculateTeacherFingerprintStats(records, totalTeachers) {
            var stats = {
                total_records: records.length,
                checked_in: 0,
                checked_out: 0,
                both: 0,
                unique_teachers: new Set(),
                total_teachers: totalTeachers || 0,
                chart_data: {
                    labels: [],
                    checked_in: [],
                    checked_out: [],
                    total_teachers: []
                }
            };

            records.forEach(function(rec) {
                var hasCheckIn = rec.check_in_time && rec.check_in_time.trim() !== '';
                var hasCheckOut = rec.check_out_time && rec.check_out_time.trim() !== '';
                var fingerprintId = (rec.user && rec.user.enroll_id) || (rec.teacher_info && rec.teacher_info.teacherID) || '';

                if (fingerprintId) {
                    stats.unique_teachers.add(fingerprintId);
                }

                if (hasCheckIn && hasCheckOut) {
                    stats.both++;
                } else if (hasCheckIn) {
                    stats.checked_in++;
                } else if (hasCheckOut) {
                    stats.checked_out++;
                }

                // Chart data by date
                if (rec.attendance_date) {
                    var dateLabel = rec.attendance_date;
                    var dateIndex = stats.chart_data.labels.indexOf(dateLabel);
                    
                    if (dateIndex === -1) {
                        dateIndex = stats.chart_data.labels.length;
                        stats.chart_data.labels.push(dateLabel);
                        stats.chart_data.checked_in.push(0);
                        stats.chart_data.checked_out.push(0);
                        stats.chart_data.total_teachers.push(totalTeachers || 0);
                    }

                    if (hasCheckIn) stats.chart_data.checked_in[dateIndex]++;
                    if (hasCheckOut) stats.chart_data.checked_out[dateIndex]++;
                }
            });

            stats.unique_teachers_count = stats.unique_teachers.size;
            stats.teachers_with_attendance = stats.unique_teachers_count;
            stats.teachers_without_attendance = Math.max(0, stats.total_teachers - stats.teachers_with_attendance);
            stats.attendance_rate = stats.total_teachers > 0 ? 
                ((stats.teachers_with_attendance / stats.total_teachers) * 100).toFixed(1) : 0;
            stats.present_rate = stats.total_teachers > 0 ?
                (((stats.checked_in + stats.both) / stats.total_teachers) * 100).toFixed(1) : 0;

            return stats;
        }

        // Display Teacher Fingerprint Attendance Overview
        function displayTeacherFingerprintAttendanceOverview(stats, searchType, apiFailed = false, filteredRecords = [], totalTeachers = 0, searchMonth = null, searchYear = null) {
            var html = '';
            
            if (apiFailed) {
                html += `
                    <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Note:</strong> External API is unavailable. Showing data from local database.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            }
            
            // Check if no attendance was collected
            if (stats.total_records === 0 || stats.teachers_with_attendance === 0) {
                html += '<div class="alert alert-info text-center" role="alert">';
                html += '<i class="bi bi-info-circle"></i> <strong>No attendance collected</strong>';
                html += '</div>';
                $('#teacherFingerprintAttendanceOverviewContent').html(html);
                // Hide export buttons
                $('#exportTeacherAttendanceExcelBtn, #exportTeacherAttendancePdfBtn').hide();
                return;
            }

            // Show export buttons if search type is month or year
            if (searchType === 'month' || searchType === 'year') {
                $('#exportTeacherAttendanceExcelBtn, #exportTeacherAttendancePdfBtn').show();
            } else {
                $('#exportTeacherAttendanceExcelBtn, #exportTeacherAttendancePdfBtn').hide();
            }
            
            // Only show summary cards for day view
            if (searchType === 'day') {
                html += '<div class="row mb-3">';
                html += '<div class="col-md-3"><div class="card bg-success"><div class="card-body text-center" style="color: #ffffff;"><h4 style="color: #ffffff;">' + (stats.checked_in + stats.both) + '</h4><p class="mb-0" style="color: #ffffff;">Checked In</p><small style="color: #ffffff;">out of ' + stats.total_teachers + ' teachers</small></div></div></div>';
                html += '<div class="col-md-3"><div class="card bg-primary"><div class="card-body text-center" style="color: #ffffff;"><h4 style="color: #ffffff;">' + (stats.checked_out + stats.both) + '</h4><p class="mb-0" style="color: #ffffff;">Checked Out</p><small style="color: #ffffff;">out of ' + stats.total_teachers + ' teachers</small></div></div></div>';
                html += '<div class="col-md-3"><div class="card bg-info"><div class="card-body text-center" style="color: #ffffff;"><h4 style="color: #ffffff;">' + stats.teachers_with_attendance + '</h4><p class="mb-0" style="color: #ffffff;">Present</p><small style="color: #ffffff;">out of ' + stats.total_teachers + ' teachers</small></div></div></div>';
                html += '<div class="col-md-3"><div class="card bg-warning"><div class="card-body text-center" style="color: #ffffff;"><h4 style="color: #ffffff;">' + stats.teachers_without_attendance + '</h4><p class="mb-0" style="color: #ffffff;">Absent</p><small style="color: #ffffff;">out of ' + stats.total_teachers + ' teachers</small></div></div></div>';
                html += '</div>';

                html += '<div class="card mb-3">';
                html += '<div class="card-header bg-primary-custom text-white"><h6 class="mb-0">Summary & Comparison</h6></div>';
                html += '<div class="card-body">';
                html += '<div class="row">';
                html += '<div class="col-md-6">';
                html += '<p><strong>Total Teachers:</strong> ' + stats.total_teachers + '</p>';
                html += '<p><strong>Present:</strong> ' + stats.teachers_with_attendance + '</p>';
                html += '<p><strong>Absent:</strong> ' + stats.teachers_without_attendance + '</p>';
                html += '</div>';
                html += '<div class="col-md-6">';
                html += '<p><strong>Total Checked In:</strong> ' + (stats.checked_in + stats.both) + ' / ' + stats.total_teachers + '</p>';
                html += '<p><strong>Total Checked Out:</strong> ' + (stats.checked_out + stats.both) + ' / ' + stats.total_teachers + '</p>';
                html += '<p><strong>Attendance Rate:</strong> <span class="badge bg-success">' + stats.attendance_rate + '%</span></p>';
                html += '<p><strong>Present Rate (Checked In):</strong> <span class="badge bg-info">' + stats.present_rate + '%</span></p>';
                html += '</div>';
                html += '</div>';
                html += '</div></div>';
            }

            $('#teacherFingerprintAttendanceOverviewContent').html(html);
        }

        // Generate Teacher Fingerprint Attendance Charts
        var teacherFingerprintAttendanceChart = null;
        var teacherFingerprintStatusChart = null;

        function generateTeacherFingerprintAttendanceCharts(records, searchType, totalTeachers) {
            // Destroy existing charts if they exist
            if (teacherFingerprintAttendanceChart) {
                teacherFingerprintAttendanceChart.destroy();
            }
            if (teacherFingerprintStatusChart) {
                teacherFingerprintStatusChart.destroy();
            }

            // Check if Chart.js is available
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js is not loaded. Please include Chart.js library.');
                return;
            }

            var ctx1 = document.getElementById('teacherFingerprintAttendanceChart');
            var ctx2 = document.getElementById('teacherFingerprintStatusChart');

            if (!ctx1 || !ctx2) return;

            // Calculate chart data
            var stats = calculateTeacherFingerprintStats(records, totalTeachers);

            // For month/year, show bar chart and pie chart
            if (searchType === 'month' || searchType === 'year') {
                var presentPercent = stats.total_teachers > 0 ? ((stats.teachers_with_attendance / stats.total_teachers) * 100).toFixed(1) : 0;
                var absentPercent = stats.total_teachers > 0 ? ((stats.teachers_without_attendance / stats.total_teachers) * 100).toFixed(1) : 0;

                // Show both charts
                if (ctx1 && ctx1.parentElement) {
                    ctx1.parentElement.parentElement.style.display = 'block';
                }
                if (ctx2 && ctx2.parentElement) {
                    ctx2.parentElement.parentElement.style.display = 'block';
                }

                // Bar Chart - Present vs Absent (count)
                teacherFingerprintAttendanceChart = new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: ['Present', 'Absent'],
                        datasets: [{
                            label: 'Number of Teachers',
                            data: [stats.teachers_with_attendance, stats.teachers_without_attendance],
                            backgroundColor: ['#28a745', '#dc3545']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.y + ' teachers';
                                    }
                                }
                            }
                        }
                    }
                });

                // Status Distribution Chart (Pie Chart) - Present vs Absent
                teacherFingerprintStatusChart = new Chart(ctx2, {
                    type: 'pie',
                    data: {
                        labels: ['Present (' + presentPercent + '%)', 'Absent (' + absentPercent + '%)'],
                        datasets: [{
                            data: [stats.teachers_with_attendance, stats.teachers_without_attendance],
                            backgroundColor: [
                                '#28a745',
                                '#dc3545'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        var label = context.label || '';
                                        var value = context.parsed || 0;
                                        var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        var percentage = ((value / total) * 100).toFixed(1);
                                        return label + ': ' + value + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                // For day view, show both charts
                if (ctx1 && ctx1.parentElement) {
                    ctx1.parentElement.parentElement.style.display = 'block';
                }
                if (ctx2 && ctx2.parentElement) {
                    ctx2.parentElement.parentElement.style.display = 'block';
                }

                // Attendance Chart (Bar Chart)
                teacherFingerprintAttendanceChart = new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: stats.chart_data.labels || [],
                        datasets: [{
                            label: 'Checked In',
                            data: stats.chart_data.checked_in || [],
                            backgroundColor: '#28a745'
                        }, {
                            label: 'Checked Out',
                            data: stats.chart_data.checked_out || [],
                            backgroundColor: '#007bff'
                        }, {
                            label: 'Total Teachers',
                            data: stats.chart_data.total_teachers || [],
                            backgroundColor: '#ffc107',
                            type: 'line',
                            borderColor: '#ffc107',
                            borderWidth: 2,
                            fill: false,
                            pointRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: Math.max(totalTeachers || 0, ...(stats.chart_data.checked_in || []), ...(stats.chart_data.checked_out || []))
                            }
                        }
                    }
                });

                // Status Distribution Chart (Pie Chart)
                teacherFingerprintStatusChart = new Chart(ctx2, {
                    type: 'pie',
                    data: {
                        labels: ['Present', 'Absent'],
                        datasets: [{
                            data: [stats.teachers_with_attendance, stats.teachers_without_attendance],
                            backgroundColor: [
                                '#28a745',
                                '#dc3545'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        var label = context.label || '';
                                        var value = context.parsed || 0;
                                        var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        var percentage = ((value / total) * 100).toFixed(1);
                                        return label + ': ' + value + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        // Display Teacher Attendance Table
        function displayTeacherAttendanceTable(records, totalTeachers, searchMonth, searchYear) {
            // Calculate working days (excluding weekends)
            var startDate, endDate;
            if (searchMonth) {
                var monthParts = searchMonth.split('-');
                startDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]) - 1, 1);
                endDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]), 0);
                if (endDate > new Date()) endDate = new Date();
            } else if (searchYear) {
                startDate = new Date(parseInt(searchYear), 0, 1);
                endDate = new Date(parseInt(searchYear), 11, 31);
                if (endDate > new Date()) endDate = new Date();
            } else {
                return ''; // No table for day view
            }

            // Calculate working days
            var workingDays = 0;
            var current = new Date(startDate);
            while (current <= endDate) {
                if (current.getDay() !== 0 && current.getDay() !== 6) { // Not Sunday or Saturday
                    workingDays++;
                }
                current.setDate(current.getDate() + 1);
            }

            // Group records by teacher
            var teacherMap = {};
            records.forEach(function(rec) {
                var teacherId = (rec.teacher_info && rec.teacher_info.teacherID) || (rec.user && rec.user.enroll_id) || '';
                if (!teacherId) return;

                if (!teacherMap[teacherId]) {
                    teacherMap[teacherId] = {
                        id: teacherId,
                        name: (rec.teacher_info && rec.teacher_info.full_name) || (rec.user && rec.user.name) || 'N/A',
                        position: (rec.teacher_info && rec.teacher_info.position) || 'N/A',
                        presentDates: new Set()
                    };
                }

                if (rec.check_in_time && rec.check_in_time.trim() !== '') {
                    teacherMap[teacherId].presentDates.add(rec.attendance_date);
                }
            });

            // Get all teachers from blade (passed from controller)
            var allTeachers = @json($teachers ?? []);
            
            // Build complete teacher list
            var completeTeacherList = [];
            allTeachers.forEach(function(teacher) {
                var teacherId = teacher.id || teacher.fingerprint_id || '';
                var fullName = (teacher.first_name || '') + ' ' + (teacher.middle_name ? teacher.middle_name + ' ' : '') + (teacher.last_name || '');
                fullName = fullName.trim() || 'N/A';
                
                var teacherData = teacherMap[teacherId] || {
                    id: teacherId,
                    name: fullName,
                    position: teacher.position || 'N/A',
                    presentDates: new Set()
                };
                
                completeTeacherList.push(teacherData);
            });

            // Sort by name
            completeTeacherList.sort(function(a, b) {
                return a.name.localeCompare(b.name);
            });

            var html = '<div class="card mt-4">';
            html += '<div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center">';
            html += '<h6 class="mb-0"><i class="bi bi-table"></i> Teacher Attendance Records</h6>';
            html += '</div>';
            html += '<div class="card-body">';
            html += '<div class="table-responsive">';
            html += '<table class="table table-striped table-hover" id="teacherAttendanceOverviewTable">';
            html += '<thead class="bg-light">';
            html += '<tr>';
            html += '<th>#</th>';
            html += '<th>Teacher Name</th>';
            html += '<th>Position</th>';
            html += '<th>Days Present</th>';
            html += '<th>Days Absent</th>';
            html += '<th>Working Days</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';

            completeTeacherList.forEach(function(teacher, index) {
                var daysPresent = teacher.presentDates.size;
                var daysAbsent = Math.max(0, workingDays - daysPresent);

                html += '<tr>';
                html += '<td>' + (index + 1) + '</td>';
                html += '<td><strong>' + teacher.name + '</strong></td>';
                html += '<td>' + teacher.position + '</td>';
                html += '<td><span class="badge bg-success">' + daysPresent + '</span></td>';
                html += '<td><span class="badge bg-danger">' + daysAbsent + '</span></td>';
                html += '<td>' + workingDays + '</td>';
                html += '</tr>';
            });

            html += '</tbody>';
            html += '</table>';
            html += '</div>';
            html += '</div>';
            html += '</div>';

            // Add attendance records table
            if (attendanceRecordsList.length > 0) {
                html += '<div class="card mt-4">';
                html += '<div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center">';
                html += '<h6 class="mb-0"><i class="bi bi-list-check"></i> Attendance Records</h6>';
                html += '</div>';
                html += '<div class="card-body">';
                html += '<div class="table-responsive">';
                html += '<table class="table table-striped table-hover" id="teacherAttendanceRecordsTable">';
                html += '<thead class="bg-light">';
                html += '<tr>';
                html += '<th>#</th>';
                html += '<th>Teacher Name</th>';
                html += '<th>Date</th>';
                html += '<th>Check In</th>';
                html += '<th>Check Out</th>';
                html += '<th>Status</th>';
                html += '</tr>';
                html += '</thead>';
                html += '<tbody>';

                attendanceRecordsList.forEach(function(record, index) {
                    html += '<tr>';
                    html += '<td>' + (index + 1) + '</td>';
                    html += '<td><strong>' + record.teacherName + '</strong></td>';
                    html += '<td>' + record.date + '</td>';
                    html += '<td>' + (record.checkIn || '-') + '</td>';
                    html += '<td>' + (record.checkOut || '-') + '</td>';
                    html += '<td><span class="badge bg-info">' + (record.status || '-') + '</span></td>';
                    html += '</tr>';
                });

                html += '</tbody>';
                html += '</table>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
            }

            return html;
        }

        // Export Teacher Attendance to Excel (JavaScript)
        function exportTeacherAttendanceToExcel() {
            if (typeof XLSX === 'undefined') {
                Swal.fire('Error', 'Excel export library not loaded', 'error');
                return;
            }

            // Get school name and build title (caps)
            var schoolName = '{{ $school->school_name ?? "School" }}';
            var reportTitle = '';
            if (currentSearchType === 'month' && currentSearchMonth) {
                var monthParts = currentSearchMonth.split('-');
                var monthNames = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
                reportTitle = 'TEACHER ATTENDANCE IN ' + monthNames[parseInt(monthParts[1]) - 1] + ' ' + monthParts[0];
            } else if (currentSearchType === 'year' && currentSearchYear) {
                reportTitle = 'TEACHER ATTENDANCE IN ' + currentSearchYear;
            } else {
                reportTitle = 'TEACHER ATTENDANCE';
            }

            // Calculate working days
            var startDate, endDate;
            if (currentSearchMonth) {
                var monthParts = currentSearchMonth.split('-');
                startDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]) - 1, 1);
                endDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]), 0);
                if (endDate > new Date()) endDate = new Date();
            } else if (currentSearchYear) {
                startDate = new Date(parseInt(currentSearchYear), 0, 1);
                endDate = new Date(parseInt(currentSearchYear), 11, 31);
                if (endDate > new Date()) endDate = new Date();
            }

            var workingDays = 0;
            var current = new Date(startDate);
            while (current <= endDate) {
                if (current.getDay() !== 0 && current.getDay() !== 6) {
                    workingDays++;
                }
                current.setDate(current.getDate() + 1);
            }

            // Group records by teacher
            var teacherMap = {};
            currentFilteredRecords.forEach(function(rec) {
                var teacherId = (rec.teacher_info && rec.teacher_info.teacherID) || (rec.user && rec.user.enroll_id) || '';
                if (!teacherId) return;

                if (!teacherMap[teacherId]) {
                    teacherMap[teacherId] = {
                        name: (rec.teacher_info && rec.teacher_info.full_name) || (rec.user && rec.user.name) || 'N/A',
                        position: (rec.teacher_info && rec.teacher_info.position) || 'N/A',
                        presentDates: new Set()
                    };
                }

                if (rec.check_in_time && rec.check_in_time.trim() !== '') {
                    teacherMap[teacherId].presentDates.add(rec.attendance_date);
                }
            });

            // Get all teachers from blade
            var allTeachers = @json($teachers ?? []);
            
            // Build complete teacher list
            var completeTeacherList = [];
            allTeachers.forEach(function(teacher) {
                var teacherId = teacher.id || teacher.fingerprint_id || '';
                var fullName = (teacher.first_name || '') + ' ' + (teacher.middle_name ? teacher.middle_name + ' ' : '') + (teacher.last_name || '');
                fullName = fullName.trim() || 'N/A';
                
                var teacherData = teacherMap[teacherId] || {
                    name: fullName,
                    position: teacher.position || 'N/A',
                    presentDates: new Set()
                };
                
                completeTeacherList.push(teacherData);
            });

            // Sort by name
            completeTeacherList.sort(function(a, b) {
                return a.name.localeCompare(b.name);
            });

            // Create workbook
            var wb = XLSX.utils.book_new();
            var wsData = [];

            // Header rows
            wsData.push([schoolName]);
            wsData.push([reportTitle]);
            wsData.push([]);
            wsData.push(['Teacher Name', 'Position', 'Days Present', 'Days Absent', 'Working Days']);

            // Teacher summary data
            completeTeacherList.forEach(function(teacher) {
                var daysPresent = teacher.presentDates.size;
                var daysAbsent = Math.max(0, workingDays - daysPresent);
                wsData.push([teacher.name, teacher.position, daysPresent, daysAbsent, workingDays]);
            });

            var ws = XLSX.utils.aoa_to_sheet(wsData);
            
            // Merge header cells
            if (!ws['!merges']) ws['!merges'] = [];
            ws['!merges'].push({s: {r: 0, c: 0}, e: {r: 0, c: 4}});
            ws['!merges'].push({s: {r: 1, c: 0}, e: {r: 1, c: 4}});

            XLSX.utils.book_append_sheet(wb, ws, 'Teacher Attendance');
            XLSX.writeFile(wb, 'Teacher_Attendance_' + (currentSearchMonth || currentSearchYear || 'Report') + '_' + new Date().toISOString().split('T')[0] + '.xlsx');
        }

        // Export Teacher Attendance to PDF (JavaScript)
        function exportTeacherAttendanceToPdf() {
            if (typeof window.jspdf === 'undefined') {
                Swal.fire('Error', 'PDF export library not loaded', 'error');
                return;
            }

            var { jsPDF } = window.jspdf;
            var doc = new jsPDF('landscape');

            // Get school name and build title (caps) - avoid repeating school name
            var schoolName = '{{ $school->school_name ?? "School" }}';
            var reportTitle = '';
            if (currentSearchType === 'month' && currentSearchMonth) {
                var monthParts = currentSearchMonth.split('-');
                var monthNames = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
                reportTitle = 'TEACHER ATTENDANCE IN ' + monthNames[parseInt(monthParts[1]) - 1] + ' ' + monthParts[0];
            } else if (currentSearchType === 'year' && currentSearchYear) {
                reportTitle = 'TEACHER ATTENDANCE IN ' + currentSearchYear;
            } else {
                reportTitle = 'TEACHER ATTENDANCE';
            }

            // Calculate working days
            var startDate, endDate;
            if (currentSearchMonth) {
                var monthParts = currentSearchMonth.split('-');
                startDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]) - 1, 1);
                endDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]), 0);
                if (endDate > new Date()) endDate = new Date();
            } else if (currentSearchYear) {
                startDate = new Date(parseInt(currentSearchYear), 0, 1);
                endDate = new Date(parseInt(currentSearchYear), 11, 31);
                if (endDate > new Date()) endDate = new Date();
            }

            var workingDays = 0;
            var current = new Date(startDate);
            while (current <= endDate) {
                if (current.getDay() !== 0 && current.getDay() !== 6) {
                    workingDays++;
                }
                current.setDate(current.getDate() + 1);
            }

            // Group records by teacher
            var teacherMap = {};
            currentFilteredRecords.forEach(function(rec) {
                var teacherId = (rec.teacher_info && rec.teacher_info.teacherID) || (rec.user && rec.user.enroll_id) || '';
                if (!teacherId) return;

                if (!teacherMap[teacherId]) {
                    teacherMap[teacherId] = {
                        name: (rec.teacher_info && rec.teacher_info.full_name) || (rec.user && rec.user.name) || 'N/A',
                        position: (rec.teacher_info && rec.teacher_info.position) || 'N/A',
                        presentDates: new Set()
                    };
                }

                if (rec.check_in_time && rec.check_in_time.trim() !== '') {
                    teacherMap[teacherId].presentDates.add(rec.attendance_date);
                }
            });

            // Get all teachers from blade
            var allTeachers = @json($teachers ?? []);
            
            // Build complete teacher list
            var completeTeacherList = [];
            allTeachers.forEach(function(teacher) {
                var teacherId = teacher.id || teacher.fingerprint_id || '';
                var fullName = (teacher.first_name || '') + ' ' + (teacher.middle_name ? teacher.middle_name + ' ' : '') + (teacher.last_name || '');
                fullName = fullName.trim() || 'N/A';
                
                var teacherData = teacherMap[teacherId] || {
                    name: fullName,
                    position: teacher.position || 'N/A',
                    presentDates: new Set()
                };
                
                completeTeacherList.push(teacherData);
            });

            // Sort by name
            completeTeacherList.sort(function(a, b) {
                return a.name.localeCompare(b.name);
            });

            // Add header with logo and centered titles (like student PDF)
            var pageWidth = doc.internal.pageSize.getWidth();
            var centerX = pageWidth / 2;
            var schoolLogoUrl = '{{ $school->school_logo ? asset($school->school_logo) : "" }}';

            function drawTeacherHeaderAndTable(logoImg) {
                // Logo on the left if available
                if (logoImg) {
                    try {
                        doc.addImage(logoImg, 'PNG', 14, 10, 24, 24);
                    } catch (e) {
                        console.warn('Failed to add logo to teacher PDF:', e);
                    }
                }

                // School name and report title centered
                doc.setFontSize(16);
                doc.text(schoolName.toUpperCase(), centerX, 18, { align: 'center' });
                doc.setFontSize(12);
                doc.text(reportTitle, centerX, 26, { align: 'center' });

                // Prepare summary table data
                var summaryTableData = [];
                completeTeacherList.forEach(function(teacher) {
                    var daysPresent = teacher.presentDates.size;
                    var daysAbsent = Math.max(0, workingDays - daysPresent);
                    summaryTableData.push([teacher.name, teacher.position, daysPresent, daysAbsent, workingDays]);
                });

                // Add summary table and footer
                doc.autoTable({
                    startY: 34,
                    head: [['Teacher Name', 'Position', 'Days Present', 'Days Absent', 'Working Days']],
                    body: summaryTableData,
                    theme: 'striped',
                    headStyles: { fillColor: [148, 0, 0] },
                    didDrawPage: function (data) {
                        var pageHeight = doc.internal.pageSize.getHeight();
                        doc.setFontSize(9);
                        doc.text('shuleLink powered by emcaTechnology', centerX, pageHeight - 8, { align: 'center' });
                    }
                });

                // Save PDF
                doc.save('Teacher_Attendance_' + (currentSearchMonth || currentSearchYear || 'Report') + '_' + new Date().toISOString().split('T')[0] + '.pdf');
            }

            if (schoolLogoUrl) {
                var img = new Image();
                img.crossOrigin = 'Anonymous';
                img.onload = function() {
                    drawTeacherHeaderAndTable(img);
                };
                img.onerror = function() {
                    console.warn('Failed to load school logo image for teacher PDF header.');
                    drawTeacherHeaderAndTable(null);
                };
                img.src = schoolLogoUrl;
            } else {
                drawTeacherHeaderAndTable(null);
            }
        }
    });
</script>
