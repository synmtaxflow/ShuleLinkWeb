@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
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

    /* School Details Card Styles */
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

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid mt-4">
    <!-- Header Card with Buttons -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light rounded">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0 text-primary-custom">
                    <i class="bi bi-people-fill"></i> Manage Other Staff
                </h4>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-outline-primary-custom fw-bold" id="staffProfessionsBtn" type="button" data-bs-toggle="modal" data-bs-target="#staffProfessionsModal">
                        <i class="bi bi-briefcase"></i> Staff Professions
                    </button>
                    <button class="btn btn-outline-primary-custom fw-bold" id="viewStaffProfessionsBtn" type="button" data-bs-toggle="modal" data-bs-target="#viewStaffProfessionsModal">
                        <i class="bi bi-eye"></i> View Staff Professions
                    </button>
                    <button class="btn btn-outline-primary-custom fw-bold" id="staffDutiesBtn" type="button" data-bs-toggle="modal" data-bs-target="#staffDutiesModal">
                        <i class="bi bi-shield-check"></i> Staff Duties/Permissions
                    </button>
                    <button class="btn btn-outline-primary-custom fw-bold" id="addStaffBtn" type="button" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                        <i class="bi bi-person-plus"></i> Register New Staff
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="staffTable" class="table table-hover align-middle mb-0" style="width:100%">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Full Name</th>
                            <th>Profession</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Fingerprint ID</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($otherStaff) > 0)
                            @foreach ($otherStaff as $staff)
                                <tr data-staff-id="{{ $staff->id }}">
                                    <td>
                                        @php
                                            $imgPath = $staff->image
                                                ? asset('userImages/' . $staff->image)
                                                : ($staff->gender == 'Female'
                                                    ? asset('images/female.png')
                                                    : asset('images/male.png'));
                                        @endphp
                                        <img src="{{ $imgPath }}" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover; border: 3px solid #940000;" alt="Staff">
                                    </td>
                                    <td><strong>{{ $staff->first_name }} {{ $staff->middle_name }} {{ $staff->last_name }}</strong></td>
                                    <td>
                                        @if($staff->profession)
                                            <span class="badge bg-primary text-white">{{ $staff->profession->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $staff->email }}</td>
                                    <td>{{ $staff->phone_number }}</td>
                                    <td>
                                        @if($staff->fingerprint_id)
                                            <span class="badge bg-info text-white" style="font-size: 0.9rem; font-weight: bold;">
                                                <i class="bi bi-fingerprint"></i> {{ $staff->fingerprint_id }}
                                            </span>
                                        @else
                                            <span class="text-muted" style="font-style: italic;">
                                                <i class="bi bi-dash-circle"></i> Not assigned
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ strtolower($staff->status) == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $staff->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center align-items-center gap-2">
                                            <a href="#" class="btn btn-sm btn-info text-white view-staff-btn"
                                               data-staff-id="{{ $staff->id }}"
                                               title="View Details">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-warning text-dark edit-staff-btn"
                                               data-staff-id="{{ $staff->id }}"
                                               title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-success text-white send-to-fingerprint-btn"
                                               data-staff-id="{{ $staff->id }}"
                                               data-staff-name="{{ $staff->first_name }}"
                                               title="Send to Fingerprint Device">
                                                <i class="bi bi-fingerprint"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-danger text-white delete-staff-btn"
                                               data-staff-id="{{ $staff->id }}"
                                               data-staff-name="{{ $staff->first_name }} {{ $staff->last_name }}"
                                               title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <!-- Hidden full details for view modal -->
                                    <div style="display:none;" class="staff-full-details" data-staff-id="{{ $staff->id }}">
                                        @php
                                            $staffImgPath = $staff->image
                                                ? asset('userImages/' . $staff->image)
                                                : ($staff->gender == 'Female'
                                                    ? asset('images/female.png')
                                                    : asset('images/male.png'));
                                        @endphp
                                        <div class="p-3">
                                            <div class="school-details-card">
                                                <div class="school-header">
                                                    <div class="d-flex align-items-center">
                                                        <div class="school-logo-preview me-3">
                                                            <img src="{{ $staffImgPath }}" alt="{{ $staff->first_name }} {{ $staff->last_name }}">
                                                        </div>
                                                        <div>
                                                            <h3 class="school-title">{{ $staff->first_name }} {{ $staff->middle_name }} {{ $staff->last_name }}</h3>
                                                            <small class="text-muted">Employee: {{ $staff->employee_number }}</small>
                                                        </div>
                                                    </div>
                                                    <span class="badge {{ strtolower($staff->status) == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                                        {{ $staff->status }}
                                                    </span>
                                                </div>
                                                <div class="school-info-grid">
                                                    <div class="info-item">
                                                        <i class="bi bi-briefcase"></i>
                                                        <div class="info-item-content">
                                                            <div class="info-item-label">Profession</div>
                                                            <div class="info-item-value">{{ $staff->profession->name ?? '-' }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="info-item">
                                                        <i class="bi bi-gender-ambiguous"></i>
                                                        <div class="info-item-content">
                                                            <div class="info-item-label">Gender</div>
                                                            <div class="info-item-value">{{ $staff->gender }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="info-item">
                                                        <i class="bi bi-card-text"></i>
                                                        <div class="info-item-content">
                                                            <div class="info-item-label">National ID</div>
                                                            <div class="info-item-value">{{ $staff->national_id }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="info-item">
                                                        <i class="bi bi-envelope"></i>
                                                        <div class="info-item-content">
                                                            <div class="info-item-label">Email</div>
                                                            <div class="info-item-value">{{ $staff->email }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="info-item">
                                                        <i class="bi bi-phone"></i>
                                                        <div class="info-item-content">
                                                            <div class="info-item-label">Phone</div>
                                                            <div class="info-item-value">{{ $staff->phone_number }}</div>
                                                        </div>
                                                    </div>
                                                    @if($staff->bank_account_number)
                                                    <div class="info-item">
                                                        <i class="bi bi-bank"></i>
                                                        <div class="info-item-content">
                                                            <div class="info-item-label">Bank Account</div>
                                                            <div class="info-item-value">{{ $staff->bank_account_number }}</div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    @if($staff->address)
                                                    <div class="info-item">
                                                        <i class="bi bi-geo-alt"></i>
                                                        <div class="info-item-content">
                                                            <div class="info-item-label">Address</div>
                                                            <div class="info-item-value">{{ $staff->address }}</div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    @if($staff->qualification)
                                                    <div class="info-item">
                                                        <i class="bi bi-mortarboard"></i>
                                                        <div class="info-item-content">
                                                            <div class="info-item-label">Qualification</div>
                                                            <div class="info-item-value">{{ $staff->qualification }}</div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="text-center text-muted">No staff members found</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODALS ==================== -->

{{-- Staff Professions Modal --}}
<div class="modal fade" id="staffProfessionsModal" tabindex="-1" aria-labelledby="staffProfessionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="staffProfessionForm">
                @csrf
                <div class="modal-header bg-primary-custom text-white">
                    <h5 class="modal-title" id="staffProfessionsModalLabel">
                        <i class="bi bi-briefcase-fill"></i> Staff Profession
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="profession_id" id="profession_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Profession Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="profession_name" class="form-control" placeholder="e.g., IT, Accountant, HR" required>
                        <small class="text-muted">Enter the profession title (e.g., IT, Accountant, HR, Secretary)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" id="profession_description" class="form-control" rows="3" placeholder="Optional description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Save Profession
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View Staff Professions Modal --}}
<div class="modal fade" id="viewStaffProfessionsModal" tabindex="-1" aria-labelledby="viewStaffProfessionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewStaffProfessionsModalLabel">
                    <i class="bi bi-eye"></i> View Staff Professions
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="professionsTable" class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Profession Name</th>
                                <th>Description</th>
                                <th>Duties/Permissions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($professions) > 0)
                                @foreach($professions as $profession)
                                    <tr data-profession-id="{{ $profession->id }}">
                                        <td><strong>{{ $profession->name }}</strong></td>
                                        <td>{{ $profession->description ?? '-' }}</td>
                                        <td>
                                            @if($profession->permissions && count($profession->permissions) > 0)
                                                <span class="badge bg-info text-white">{{ count($profession->permissions) }} permission(s)</span>
                                            @else
                                                <span class="text-muted">No permissions assigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-primary edit-profession-btn" data-profession-id="{{ $profession->id }}" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger delete-profession-btn" data-profession-id="{{ $profession->id }}" data-profession-name="{{ $profession->name }}" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No professions found</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Staff Duties/Permissions Modal --}}
<div class="modal fade" id="staffDutiesModal" tabindex="-1" aria-labelledby="staffDutiesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form id="staffDutiesForm">
                @csrf
                <input type="hidden" name="profession_id" id="duties_profession_id">
                <div class="modal-header bg-primary-custom text-white">
                    <h5 class="modal-title" id="staffDutiesModalLabel">
                        <i class="bi bi-shield-check"></i> Staff Duties/Permissions
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Profession <span class="text-danger">*</span></label>
                        <select name="profession_id_select" id="duties_profession_select" class="form-select" required>
                            <option value="">Choose a profession...</option>
                            @if(count($professions) > 0)
                                @foreach($professions as $profession)
                                    <option value="{{ $profession->id }}">{{ $profession->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <small class="text-muted">Select the profession to assign duties/permissions</small>
                    </div>

                    <div class="border rounded p-3" style="max-height: 500px; overflow-y: auto;">
                        <h6 class="mb-3 text-primary-custom">Select Permissions/Duties</h6>
                        @php
                            $permissionCategories = [
                                'Examination Management' => 'examination',
                                'Classes Management' => 'classes',
                                'Subject Management' => 'subject',
                                'Result Management' => 'result',
                                'Attendance Management' => 'attendance',
                                'Student Management' => 'student',
                                'Parent Management' => 'parent',
                                'Timetable Management' => 'timetable',
                                'Teacher Management' => 'teacher',
                                'Fees Management' => 'fees',
                                'School Resources' => 'resources',
                                'Revenue Management' => 'revenue',
                                'Expenses Management' => 'expenses',
                                'Accommodation Management' => 'accommodation',
                                'Library Management' => 'library',
                                'Calendar Management' => 'calendar',
                                'Fingerprint Settings' => 'fingerprint',
                                'Task Management' => 'task',
                                'SMS Information' => 'sms',
                            ];
                            $permissionActions = ['create', 'update', 'delete', 'read_only'];
                        @endphp

                        @foreach($permissionCategories as $categoryName => $categoryKey)
                            <div class="mb-4 border-bottom pb-3">
                                <h6 class="fw-bold text-primary-custom mb-3">
                                    <i class="bi bi-folder"></i> {{ $categoryName }}
                                </h6>
                                <div class="row g-2">
                                    @foreach($permissionActions as $action)
                                        @php
                                            $permissionName = $categoryKey . '_' . $action;
                                            $permissionId = 'permission_' . $permissionName;
                                        @endphp
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox"
                                                       type="checkbox"
                                                       name="permissions[]"
                                                       value="{{ $permissionName }}"
                                                       id="{{ $permissionId }}">
                                                <label class="form-check-label" for="{{ $permissionId }}">
                                                    {{ ucfirst(str_replace('_', ' ', $action)) }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Save Duties/Permissions
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Staff Modal --}}
<div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form id="addStaffForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary-custom text-white">
                    <h5 class="modal-title" id="addStaffModalLabel">
                        <i class="bi bi-person-plus-fill"></i> Register New Staff
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
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
                            <input type="text" name="phone_number" class="form-control" pattern="^255\d{9}$" placeholder="255614863345" required maxlength="12">
                            <small class="text-muted">Must start with 255 followed by 9 digits (12 digits total, e.g., 255614863345)</small>
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
                            <label class="form-label fw-bold">Staff Profession</label>
                            <select name="profession_id" class="form-select">
                                <option value="">Select Profession...</option>
                                @if(count($professions) > 0)
                                    @foreach($professions as $profession)
                                        <option value="{{ $profession->id }}">{{ $profession->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Bank Account Number</label>
                            <input type="text" name="bank_account_number" class="form-control" placeholder="e.g., 1234567890">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Qualification</label>
                            <input type="text" name="qualification" class="form-control" placeholder="e.g., Diploma">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Specialization</label>
                            <input type="text" name="specialization" class="form-control" placeholder="e.g., IT Support">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Experience</label>
                            <input type="text" name="experience" class="form-control" placeholder="e.g., 3 years">
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
                            <input type="text" name="position" class="form-control" placeholder="e.g., IT Officer">
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
                            <label class="form-label fw-bold">Staff Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Supported formats: JPG, PNG (Max: 2MB)</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Register Staff
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Staff Modal --}}
<div class="modal fade" id="editStaffModal" tabindex="-1" aria-labelledby="editStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form id="editStaffForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="staff_id" id="edit_staff_id">
                <div class="modal-header bg-primary-custom text-white">
                    <h5 class="modal-title" id="editStaffModalLabel">
                        <i class="bi bi-pencil-square"></i> Edit Staff
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
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
                            <input type="text" name="phone_number" id="edit_phone_number" class="form-control" pattern="^255\d{9}$" placeholder="255614863345" required maxlength="12">
                            <small class="text-muted">Must start with 255 followed by 9 digits</small>
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
                            <label class="form-label fw-bold">Staff Profession</label>
                            <select name="profession_id" id="edit_profession_id" class="form-select">
                                <option value="">Select Profession...</option>
                                @if(count($professions) > 0)
                                    @foreach($professions as $profession)
                                        <option value="{{ $profession->id }}">{{ $profession->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Bank Account Number</label>
                            <input type="text" name="bank_account_number" id="edit_bank_account_number" class="form-control" placeholder="e.g., 1234567890">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Qualification</label>
                            <input type="text" name="qualification" id="edit_qualification" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Specialization</label>
                            <input type="text" name="specialization" id="edit_specialization" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Experience</label>
                            <input type="text" name="experience" id="edit_experience" class="form-control">
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
                            <input type="text" name="position" id="edit_position" class="form-control">
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
                            <input type="text" name="address" id="edit_address" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Staff Image</label>
                            <input type="file" name="image" id="edit_image" class="form-control" accept="image/*">
                            <small class="text-muted">Leave empty to keep current image</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Update Staff
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View Staff Modal --}}
<div class="modal fade" id="viewStaffModal" tabindex="-1" aria-labelledby="viewStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewStaffModalLabel">
                    <i class="bi bi-eye"></i> Staff Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewStaffModalBody">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<!-- DataTables JS -->
<!-- jQuery removed here to avoid duplicate include; use jQuery loaded in footer -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
console.log('=== SCRIPT LOADED ===');
console.log('jQuery loaded:', typeof jQuery !== 'undefined');
console.log('$ loaded:', typeof $ !== 'undefined');

$(document).ready(function() {
    console.log('=== DOCUMENT READY FIRED ===');

    // Initialize DataTable
    var staffTable = $('#staffTable').DataTable({
        "order": [[1, "asc"]],
        "pageLength": 25,
        "dom": 'Bfrtip',
        "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });

    // Initialize modals
    var staffProfessionsModal = null;
    var viewStaffProfessionsModal = null;
    var staffDutiesModal = null;
    var addStaffModal = null;
    var editStaffModal = null;
    var viewStaffModal = null;

    // Initialize Bootstrap modals
    if (typeof bootstrap !== 'undefined') {
        if (document.getElementById('staffProfessionsModal')) {
            staffProfessionsModal = new bootstrap.Modal(document.getElementById('staffProfessionsModal'));
        }
        if (document.getElementById('viewStaffProfessionsModal')) {
            viewStaffProfessionsModal = new bootstrap.Modal(document.getElementById('viewStaffProfessionsModal'));
        }
        if (document.getElementById('staffDutiesModal')) {
            staffDutiesModal = new bootstrap.Modal(document.getElementById('staffDutiesModal'));
        }
        if (document.getElementById('addStaffModal')) {
            addStaffModal = new bootstrap.Modal(document.getElementById('addStaffModal'));
        }
        if (document.getElementById('editStaffModal')) {
            editStaffModal = new bootstrap.Modal(document.getElementById('editStaffModal'));
        }
        if (document.getElementById('viewStaffModal')) {
            viewStaffModal = new bootstrap.Modal(document.getElementById('viewStaffModal'));
        }
    }

    // CSRF Token setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // ==================== STAFF PROFESSION HANDLERS ====================

    // Debug: Check if form exists
    console.log('Form check:', $('#staffProfessionForm').length);
    console.log('Form element:', document.getElementById('staffProfessionForm'));

    // ATTACH FORM HANDLER - Use event delegation to ensure it works even if form is in modal
    $(document).on('submit', '#staffProfessionForm', function(e) {
        // CRITICAL: Prevent default form submission FIRST
        e.preventDefault();
        e.stopPropagation(); // Stop bubbling

        console.log('=== FORM SUBMIT EVENT FIRED ===');

        // Verify form exists and gets the correct form
        var $form = $(this);
        console.log('Form ID:', $form.attr('id'));
        
        var formData = $form.serialize();
        var url = '{{ route("save_staff_profession") }}';
        var method = 'POST';
        var professionId = $('#profession_id').val();

        if (professionId) {
            // Update existing
            url = '{{ route("update_staff_profession") }}';
        }

        // Verify CSRF token
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (!csrfToken) {
            console.error('CSRF token not found!');
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'CSRF token not found. Please refresh the page.'
            });
            return false;
        }

        console.log('Submitting profession form to:', url);
        console.log('Method:', method);
        console.log('Data:', formData);

        var $submitBtn = $form.find('button[type="submit"]');
        var originalText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

        $.ajax({
            url: url,
            type: method,
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log('AJAX Success Response:', response);
                $submitBtn.prop('disabled', false).html(originalText);
                
                // Close modal first
                if (typeof bootstrap !== 'undefined' && staffProfessionsModal) {
                    staffProfessionsModal.hide();
                } else {
                    $('#staffProfessionsModal').modal('hide');
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.success || 'Profession saved successfully!',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr, status, error) {
                $submitBtn.prop('disabled', false).html(originalText);
                console.error('AJAX Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });

                // Detailed error handling
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
                    var errorMsg = Object.values(errors).join('<br>'); // Join all errors
                    if (!errorMsg && xhr.responseJSON && xhr.responseJSON.error) {
                         errorMsg = xhr.responseJSON.error;
                    }
                    if (!errorMsg) errorMsg = 'Validation failed.';

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: errorMsg
                    });
                } else {
                     var errorMsg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'An error occurred. check console.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                }
            }
        });

        return false; // Extra safety
    });

    // Handle Edit Profession Button
    $(document).on('click', '.edit-profession-btn', function() {
        var professionId = $(this).data('profession-id');
        var $btn = $(this);
        var originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Loading...');

        $.ajax({
            url: '{{ url("get_staff_profession") }}/' + professionId,
            method: 'GET',
            success: function(response) {
                $btn.prop('disabled', false).html(originalText);
                var profession = response.profession;
                $('#profession_id').val(profession.id);
                $('#profession_name').val(profession.name);
                $('#profession_description').val(profession.description || '');
                $('#staffProfessionsModalLabel').html('<i class="bi bi-pencil-square"></i> Edit Profession');
                if (staffProfessionsModal) {
                    staffProfessionsModal.show();
                } else {
                    $('#staffProfessionsModal').modal('show');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html(originalText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load profession details'
                });
            }
        });
    });

    // Handle Delete Profession
    $(document).on('click', '.delete-profession-btn', function() {
        var professionId = $(this).data('profession-id');
        var professionName = $(this).data('profession-name');

        Swal.fire({
            title: 'Delete Profession?',
            text: `Are you sure you want to delete "${professionName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("delete_staff_profession") }}/' + professionId,
                    method: 'DELETE',
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.success || 'Profession deleted successfully!',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON.error || 'Failed to delete profession'
                        });
                    }
                });
            }
        });
    });

    // Reset Staff Profession Form when modal is hidden
    $('#staffProfessionsModal').on('hidden.bs.modal', function() {
        $('#staffProfessionForm')[0].reset();
        $('#profession_id').val('');
        $('#staffProfessionsModalLabel').html('<i class="bi bi-briefcase-fill"></i> Staff Profession');
    });

    // ==================== STAFF DUTIES/PERMISSIONS HANDLERS ====================

    // Handle Staff Duties Form Submit
    $('#staffDutiesForm').on('submit', function(e) {
        e.preventDefault();
        var professionId = $('#duties_profession_select').val();
        if (!professionId) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please select a profession first'
            });
            return;
        }

        var permissions = [];
        $('.permission-checkbox:checked').each(function() {
            permissions.push($(this).val());
        });

        if (permissions.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please select at least one permission/duty'
            });
            return;
        }

        var $submitBtn = $(this).find('button[type="submit"]');
        var originalText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

        $.ajax({
            url: '{{ route("save_staff_permissions") }}',
            method: 'POST',
            data: {
                profession_id: professionId,
                permissions: permissions
            },
            success: function(response) {
                $submitBtn.prop('disabled', false).html(originalText);
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.success || 'Permissions assigned successfully!',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    $('#staffDutiesModal').modal('hide');
                    location.reload();
                });
            },
            error: function(xhr) {
                $submitBtn.prop('disabled', false).html(originalText);
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors || {};
                    var errorMsg = Object.values(errors).join('<br>');
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: errorMsg
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON.error || 'An error occurred'
                    });
                }
            }
        });
    });

    // Load permissions when profession is selected in duties modal
    $('#duties_profession_select').on('change', function() {
        var professionId = $(this).val();
        if (!professionId) {
            $('.permission-checkbox').prop('checked', false);
            return;
        }

        $.ajax({
            url: '{{ url("get_staff_profession_with_permissions") }}/' + professionId,
            method: 'GET',
            success: function(response) {
                // Uncheck all permissions first
                $('.permission-checkbox').prop('checked', false);

                // Check permissions that are assigned to this profession
                if (response.profession && response.profession.permissions) {
                    response.profession.permissions.forEach(function(permission) {
                        $('input[value="' + permission.name + '"]').prop('checked', true);
                    });
                }
            },
            error: function() {
                // If profession has no permissions yet, just uncheck all
                $('.permission-checkbox').prop('checked', false);
            }
        });
    });

    // ==================== STAFF CRUD HANDLERS ====================

    // Handle Add Staff Form Submit
    $('#addStaffForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        var $submitBtn = $(this).find('button[type="submit"]');
        var originalText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

        $.ajax({
            url: '{{ route("save_other_staff") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $submitBtn.prop('disabled', false).html(originalText);
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.success || 'Staff registered successfully!',
                    html: response.fingerprint_id ?
                        'Staff registered successfully!<br><strong>Fingerprint ID:</strong> ' + response.fingerprint_id :
                        'Staff registered successfully!',
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    $('#addStaffModal').modal('hide');
                    location.reload();
                });
            },
            error: function(xhr) {
                $submitBtn.prop('disabled', false).html(originalText);
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors || {};
                    var errorMsg = Object.values(errors).join('<br>');
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: errorMsg
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON.error || 'An error occurred'
                    });
                }
            }
        });
    });

    // Handle View Staff Button
    $(document).on('click', '.view-staff-btn', function(e) {
        e.preventDefault();
        var staffId = $(this).data('staff-id');
        var staffDetails = $('.staff-full-details[data-staff-id="' + staffId + '"]').html();

        if (staffDetails) {
            $('#viewStaffModalBody').html(staffDetails);
            if (viewStaffModal) {
                viewStaffModal.show();
            } else {
                $('#viewStaffModal').modal('show');
            }
        }
    });

// Native DOM debug listeners (temporary) to detect if clicks/submits reach the browser
(function(){
    try {
        var submitBtn = document.querySelector('#staffProfessionsModal button[type="submit"]');
        var form = document.getElementById('staffProfessionForm');
        console.log('Native debug attach:', {submitBtn: !!submitBtn, form: !!form});
        if (submitBtn) {
            submitBtn.addEventListener('click', function(ev){
                console.log('Native submit button clicked', ev);
            }, {capture:true});
        }
        if (form) {
            form.addEventListener('submit', function(ev){
                console.log('Native form submit event', ev);
            }, {capture:true});
        }
    } catch (e) {
        console.error('Native debug attach error', e);
    }
})();

    // Handle Edit Staff Button
    $(document).on('click', '.edit-staff-btn', function(e) {
        e.preventDefault();
        var staffId = $(this).data('staff-id');

        $.ajax({
            url: '{{ url("get_other_staff") }}/' + staffId,
            method: 'GET',
            success: function(response) {
                var staff = response.staff;
                $('#edit_staff_id').val(staff.id);
                $('#edit_first_name').val(staff.first_name);
                $('#edit_middle_name').val(staff.middle_name || '');
                $('#edit_last_name').val(staff.last_name);
                $('#edit_gender').val(staff.gender);
                $('#edit_email').val(staff.email);
                $('#edit_phone_number').val(staff.phone_number);
                $('#edit_national_id').val(staff.national_id);
                $('#edit_employee_number').val(staff.employee_number);
                $('#edit_profession_id').val(staff.profession_id || '');
                $('#edit_bank_account_number').val(staff.bank_account_number || '');
                $('#edit_qualification').val(staff.qualification || '');
                $('#edit_specialization').val(staff.specialization || '');
                $('#edit_experience').val(staff.experience || '');
                $('#edit_date_of_birth').val(staff.date_of_birth || '');
                $('#edit_date_hired').val(staff.date_hired || '');
                $('#edit_position').val(staff.position || '');
                $('#edit_status').val(staff.status || 'Active');
                $('#edit_address').val(staff.address || '');

                if (editStaffModal) {
                    editStaffModal.show();
                } else {
                    $('#editStaffModal').modal('show');
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load staff details'
                });
            }
        });
    });

    // Handle Edit Staff Form Submit
    $('#editStaffForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        var $submitBtn = $(this).find('button[type="submit"]');
        var originalText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

        $.ajax({
            url: '{{ route("update_other_staff") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $submitBtn.prop('disabled', false).html(originalText);
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.success || 'Staff updated successfully!',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    $('#editStaffModal').modal('hide');
                    location.reload();
                });
            },
            error: function(xhr) {
                $submitBtn.prop('disabled', false).html(originalText);
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors || {};
                    var errorMsg = Object.values(errors).join('<br>');
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: errorMsg
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON.error || 'An error occurred'
                    });
                }
            }
        });
    });

    // Handle Delete Staff Button
    $(document).on('click', '.delete-staff-btn', function(e) {
        e.preventDefault();
        var staffId = $(this).data('staff-id');
        var staffName = $(this).data('staff-name');

        Swal.fire({
            title: 'Delete Staff?',
            text: `Are you sure you want to delete "${staffName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete the staff',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ url("delete_other_staff") }}/' + staffId,
                    method: 'DELETE',
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.success || 'Staff deleted successfully!',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON.error || 'Failed to delete staff'
                        });
                    }
                });
            }
        });
    });

    // Handle Send to Fingerprint Button
    $(document).on('click', '.send-to-fingerprint-btn', function(e) {
        e.preventDefault();
        var staffId = $(this).data('staff-id');
        var staffName = $(this).data('staff-name');

        Swal.fire({
            title: 'Send to Fingerprint Device?',
            text: `Send "${staffName}" to fingerprint device?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, send!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Sending...',
                    text: 'Please wait while we send staff to fingerprint device',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route("send_staff_to_fingerprint") }}',
                    method: 'POST',
                    data: { staff_id: staffId },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message || 'Staff sent to fingerprint device successfully!',
                                html: response.fingerprint_id ?
                                    'Staff sent successfully!<br><strong>Fingerprint ID:</strong> ' + response.fingerprint_id :
                                    'Staff sent successfully!',
                                timer: 3000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning',
                                text: response.message || 'Staff sent but device registration may have failed',
                                html: response.fingerprint_id ?
                                    response.message + '<br><strong>Fingerprint ID:</strong> ' + response.fingerprint_id :
                                    response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON.message || xhr.responseJSON.error || 'Failed to send staff to device'
                        });
                    }
                });
            }
        });
    });
});
