@include('includes.teacher_nav')

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
    .widget-card {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }
    .widget-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(148, 0, 0, 0.2) !important;
    }
    .modal-header.bg-primary-custom {
        background-color: #940000 !important;
        color: #ffffff;
    }
    .exam-item:hover {
        background-color: #f8f9fa !important;
    }
    .exam-item.active {
        background-color: #fff3f3 !important;
        font-weight: 600;
    }
    /* Pulse animation for live sync indicator */
    .pulse-animation {
        animation: pulse 1.5s ease-in-out infinite;
    }
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<!-- jQuery - Load first -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<!-- Bootstrap 5 JS bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="container-fluid mt-3">
    <!-- Page Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary-custom text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-gear-fill"></i> Class Management 
                    @if(isset($subclassDisplayName) && $subclassDisplayName)
                        <span class="badge bg-light text-primary-custom ms-2">({{ $subclassDisplayName }})</span>
                    @endif
                </h5>
            </div>
        </div>
    </div>

    <!-- Action Widgets -->
    <div class="row g-4 mb-4">
        <!-- Register Student Widget -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card shadow-sm widget-card h-100" id="registerStudentBtn">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-person-plus-fill text-primary-custom" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="card-title mb-0 fw-bold">Register Student</h6>
                    <small class="text-muted">Add new student to class</small>
                </div>
            </div>
        </div>

        <!-- Parents Registration Widget -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card shadow-sm widget-card h-100" id="registerParentBtn">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-people-fill text-primary-custom" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="card-title mb-0 fw-bold">Parents Registration</h6>
                    <small class="text-muted">Register parent/guardian</small>
                </div>
            </div>
        </div>

        <!-- View Students Widget -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card shadow-sm widget-card h-100" id="viewStudentsBtn">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-people text-primary-custom" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="card-title mb-0 fw-bold">View Students</h6>
                    <small class="text-muted">View all class students</small>
                </div>
            </div>
        </div>

        <!-- View Parents Widget -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card shadow-sm widget-card h-100" id="viewParentsBtn">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-person-badge text-primary-custom" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="card-title mb-0 fw-bold">View Parents</h6>
                    <small class="text-muted">View all parents</small>
                </div>
            </div>
        </div>

        <!-- View Results Widget -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card shadow-sm widget-card h-100" id="viewResultsBtn">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-clipboard-data text-primary-custom" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="card-title mb-0 fw-bold">View Results</h6>
                    <small class="text-muted">View student results</small>
                </div>
            </div>
        </div>

        <!-- Attendance Management Widget -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card shadow-sm widget-card h-100" id="attendanceBtn">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-calendar-check text-primary-custom" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="card-title mb-0 fw-bold">Attendance</h6>
                    <small class="text-muted">Manage student attendance</small>
                </div>
            </div>
        </div>

        <!-- Subject Management Widget -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card shadow-sm widget-card h-100" id="subjectManagementBtn">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-book text-primary-custom" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="card-title mb-0 fw-bold">Subject Management</h6>
                    <small class="text-muted">Manage class subjects</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Register Student Modal -->
<div class="modal fade" id="registerStudentModal" tabindex="-1" role="dialog" aria-labelledby="registerStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="registerStudentModalLabel">
                    <i class="bi bi-person-plus-fill"></i> Register New Student
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="registerStudentForm">
                <div class="modal-body">
                    @if(isset($isCoordinatorView) && $isCoordinatorView)
                        <input type="hidden" name="classID" value="{{ $decryptedClassID }}">
                        <input type="hidden" name="isCoordinator" value="true">
                    @else
                        <input type="hidden" name="subclassID" value="{{ $decryptedSubclassID }}">
                    @endif
                    <input type="hidden" name="schoolID" value="{{ Session::get('schoolID') }}">

                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs mb-3" id="registerStudentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="student-info-tab" data-toggle="tab" href="#student-info" role="tab" aria-controls="student-info" aria-selected="true">
                                <i class="bi bi-person-fill"></i> Student Information
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="device-test-tab" data-toggle="tab" href="#device-test" role="tab" aria-controls="device-test" aria-selected="false">
                                <i class="bi bi-router-fill"></i> Device Connection Test
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="retrieve-users-tab" data-toggle="tab" href="#retrieve-users" role="tab" aria-controls="retrieve-users" aria-selected="false">
                                <i class="bi bi-download"></i> Retrieve Users from Device
                            </a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="registerStudentTabContent">
                        <!-- Student Information Tab -->
                        <div class="tab-pane fade show active" id="student-info" role="tabpanel" aria-labelledby="student-info-tab">
                            <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="middle_name">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gender">Gender <span class="text-danger">*</span></label>
                                <select class="form-control" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="admission_number">Admission Number</label>
                                <input type="text" class="form-control" id="admission_number" name="admission_number" readonly placeholder="Auto-generated">
                                <small class="form-text text-muted">Admission number will be generated automatically</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="admission_date">Admission Date</label>
                                <input type="date" class="form-control" id="admission_date" name="admission_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="parentID">Parent/Guardian</label>
                                <select class="form-control" id="parentID" name="parentID">
                                    <option value="">Select Parent (Optional)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    @if(isset($isCoordinatorView) && $isCoordinatorView)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="subclassID">Assign to Subclass <span class="text-danger">*</span></label>
                                <select class="form-control" id="subclassID" name="subclassID" required>
                                    <option value="">Select Subclass</option>
                                </select>
                                <small class="form-text text-muted">Select the subclass to assign this student to</small>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="student_photo">Student Photo</label>
                        <input type="file" class="form-control-file" id="student_photo" name="photo" accept="image/*">
                        <small class="form-text text-muted">Max size: 2MB. Formats: JPG, JPEG, PNG</small>
                        <div id="student_photo_error" class="text-danger" style="display: none;"></div>
                    </div>
                    
                    <!-- Health Information Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-heart-pulse"></i> Health Information</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="tc_is_disabled" name="is_disabled" value="1">
                                <label class="form-check-label" for="tc_is_disabled">
                                    Disabled
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="tc_has_epilepsy" name="has_epilepsy" value="1">
                                <label class="form-check-label" for="tc_has_epilepsy">
                                    Epilepsy/Seizure Disorder
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="tc_has_allergies" name="has_allergies" value="1">
                                <label class="form-check-label" for="tc_has_allergies">
                                    Allergies
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-3" id="tcAllergiesDetailsContainer" style="display: none;">
                        <label for="tc_allergies_details">Allergies Details</label>
                        <textarea class="form-control" id="tc_allergies_details" name="allergies_details" rows="2" placeholder="Please specify the allergies"></textarea>
                    </div>
                        </div>

                        <!-- Device Connection Test Tab -->
                        <div class="tab-pane fade" id="device-test" role="tabpanel" aria-labelledby="device-test-tab">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">
                                        <i class="bi bi-router-fill text-primary-custom"></i> Test Fingerprint Device Connection
                                    </h6>
                                    
                                    <div class="form-group">
                                        <label for="device_ip">Device IP Address</label>
                                        <input type="text" class="form-control" id="device_ip" name="device_ip" 
                                               value="{{ env('ZKTECO_IP', '192.168.1.108') }}" 
                                               placeholder="192.168.1.108">
                                        <small class="form-text text-muted">Enter the IP address of your ZKTeco fingerprint device</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="device_port">Device Port</label>
                                        <input type="number" class="form-control" id="device_port" name="device_port" 
                                               value="{{ env('ZKTECO_PORT', 4370) }}" 
                                               placeholder="4370">
                                        <small class="form-text text-muted">Default ZKTeco port is 4370</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="device_password">Comm Key (Password)</label>
                                        <input type="text" class="form-control" id="device_password" name="device_password" 
                                               value="{{ env('ZKTECO_PASSWORD', '0') }}" 
                                               placeholder="0">
                                        <small class="form-text text-muted">Usually 0 for no password, or your device Comm Key</small>
                                    </div>

                                    <button type="button" class="btn btn-primary-custom btn-block" id="testDeviceConnectionBtn">
                                        <i class="bi bi-wifi"></i> Test Connection
                                    </button>
                                    
                                    <!-- Connection Test Results -->
                                    <div id="connectionTestResults" class="mt-3" style="display: none;">
                                        <div class="alert" id="connectionTestAlert" role="alert">
                                            <div id="connectionTestMessage"></div>
                                        </div>
                                    </div>
                                    
                                    <small class="form-text text-muted mt-2">
                                        <i class="bi bi-info-circle"></i> Test connection to ZKTeco fingerprint device
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Retrieve Users from Device Tab -->
                        <div class="tab-pane fade" id="retrieve-users" role="tabpanel" aria-labelledby="retrieve-users-tab">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">
                                        <i class="bi bi-download text-primary-custom"></i> Retrieve Users from Fingerprint Device
                                    </h6>
                                    
                                    <div class="form-group">
                                        <label for="retrieve_device_ip">Device IP Address</label>
                                        <input type="text" class="form-control" id="retrieve_device_ip" name="retrieve_device_ip" 
                                               value="{{ env('ZKTECO_IP', '192.168.1.108') }}" 
                                               placeholder="192.168.1.108">
                                    </div>

                                    <div class="form-group">
                                        <label for="retrieve_device_port">Device Port</label>
                                        <input type="number" class="form-control" id="retrieve_device_port" name="retrieve_device_port" 
                                               value="{{ env('ZKTECO_PORT', 4370) }}" 
                                               placeholder="4370">
                                    </div>

                                    <div class="form-group">
                                        <label for="retrieve_device_password">Comm Key (Password)</label>
                                        <input type="text" class="form-control" id="retrieve_device_password" name="retrieve_device_password" 
                                               value="{{ env('ZKTECO_PASSWORD', '0') }}" 
                                               placeholder="0">
                                    </div>

                                    <button type="button" class="btn btn-primary-custom btn-block" id="retrieveUsersBtn">
                                        <i class="bi bi-download"></i> Retrieve Users from Device
                                    </button>
                                    
                                    <!-- Retrieve Users Results -->
                                    <div id="retrieveUsersResults" class="mt-3" style="display: none;">
                                        <div class="alert" id="retrieveUsersAlert" role="alert">
                                            <div id="retrieveUsersMessage"></div>
                                        </div>
                                        
                                        <!-- Users List Table -->
                                        <div id="retrievedUsersTable" class="mt-3" style="display: none;">
                                            <h6 class="mb-2">Users Found on Device:</h6>
                                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                                <table class="table table-sm table-bordered table-striped">
                                                    <thead class="bg-primary-custom text-white">
                                                        <tr>
                                                            <th>UID</th>
                                                            <th>Name</th>
                                                            <th>Privilege</th>
                                                            <th>User ID</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="retrievedUsersList">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <small class="form-text text-muted mt-2">
                                        <i class="bi bi-info-circle"></i> Retrieve all users registered on the fingerprint device
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary-custom">Register Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Register Parent Modal -->
<div class="modal fade" id="registerParentModal" tabindex="-1" role="dialog" aria-labelledby="registerParentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="registerParentModalLabel">
                    <i class="bi bi-people-fill"></i> Register Parent/Guardian
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="registerParentForm">
                <div class="modal-body">
                    <input type="hidden" name="schoolID" value="{{ Session::get('schoolID') }}">

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="parent_first_name">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="parent_first_name" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="parent_middle_name">Middle Name</label>
                                <input type="text" class="form-control" id="parent_middle_name" name="middle_name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="parent_last_name">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="parent_last_name" name="last_name" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="parent_gender">Gender</label>
                                <select class="form-control" id="parent_gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="parent_phone">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="parent_phone" name="phone" required
                                       pattern="^255[67]\d{8}$"
                                       maxlength="12"
                                       placeholder="255614863345">
                                <small class="form-text text-muted">Format: 255 + 6/7 + 8 digits (e.g., 255614863345 or 255714863345)</small>
                                <div id="parent_phone_error" class="text-danger" style="display: none;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="parent_email">Email</label>
                                <input type="email" class="form-control" id="parent_email" name="email"
                                       pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                                <small class="form-text text-muted">Enter a valid email address</small>
                                <div id="parent_email_error" class="text-danger" style="display: none;"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="parent_occupation">Occupation</label>
                                <input type="text" class="form-control" id="parent_occupation" name="occupation">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="parent_national_id">National ID</label>
                                <input type="text" class="form-control" id="parent_national_id" name="national_id">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="parent_address">Address</label>
                                <textarea class="form-control" id="parent_address" name="address" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="parent_photo">Parent Photo</label>
                        <input type="file" class="form-control-file" id="parent_photo" name="photo" accept="image/*">
                        <small class="form-text text-muted">Max size: 2MB. Formats: JPG, JPEG, PNG</small>
                        <div id="parent_photo_error" class="text-danger" style="display: none;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary-custom">Register Parent</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Fingerprint Capture Modal -->
<div class="modal fade" id="fingerprintCaptureModal" tabindex="-1" role="dialog" aria-labelledby="fingerprintCaptureModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="fingerprintCaptureModalLabel">
                    <i class="bi bi-fingerprint"></i> Fingerprint Registration
                </h5>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <h5 class="text-primary-custom" id="fingerprintStudentName"></h5>
                    <p class="text-muted mb-2">
                        <strong>Fingerprint ID:</strong> <span id="fingerprintIdDisplay" class="badge badge-primary" style="font-size: 1.3rem; padding: 0.6rem 1.2rem; font-weight: bold;"></span>
                    </p>
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle"></i> <strong>Muhimu:</strong> Tumia Fingerprint ID hii kusearch kwenye device ya biometric na uweke fingerprint kwa mwanafunzi.
                    </div>
                    <div id="fingerprintSentStatus" class="alert alert-success mt-3" style="display: none;">
                        <i class="bi bi-check-circle"></i> <span id="fingerprintSentMessage"></span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h6 class="mb-3">Please place your finger on the device three times</h6>
                    
                    <!-- Fingerprint Progress Widget -->
                    <div class="fingerprint-progress-container" style="max-width: 400px; margin: 0 auto;">
                        <div class="progress" style="height: 40px; border-radius: 20px; background-color: #e9ecef;">
                            <div id="fingerprintProgress" class="progress-bar progress-bar-striped progress-bar-animated bg-primary-custom" 
                                 role="progressbar" 
                                 style="width: 0%; transition: width 0.5s ease;"
                                 aria-valuenow="0" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <span id="fingerprintProgressText" style="line-height: 40px; font-weight: bold; color: white;">0%</span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <p id="fingerprintInstruction" class="text-muted">
                                <i class="bi bi-hand-index"></i> Place your finger on the device (1st time)
                            </p>
                        </div>
                    </div>
                </div>
                
                <div id="fingerprintSuccessMessage" class="alert alert-success" style="display: none;">
                    <i class="bi bi-check-circle-fill"></i> <strong>Success!</strong> Fingerprint captured successfully!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closeFingerprintModal" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Students Modal -->
<div class="modal fade" id="viewStudentsModal" tabindex="-1" role="dialog" aria-labelledby="viewStudentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%; width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewStudentsModalLabel">
                    <i class="bi bi-people"></i> Class Students
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-danger" id="exportStudentsPdfBtn" title="Export Students to PDF">
                        <i class="bi bi-file-pdf"></i> Export PDF
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="exportStudentsExcelBtn" title="Export Students to Excel">
                        <i class="bi bi-file-excel"></i> Export Excel
                    </button>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <div class="modal-body">
                <!-- Filters and Statistics Section -->
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h6 class="mb-3"><i class="bi bi-funnel-fill"></i> Filters</h6>
                            </div>
                            @if(isset($isCoordinatorView) && $isCoordinatorView)
                            <div class="col-md-3">
                                <label for="filterGender" class="form-label">Gender</label>
                                <select class="form-control" id="filterGender">
                                    <option value="">All Genders</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterSubclass" class="form-label">Subclass</label>
                                <select class="form-control" id="filterSubclass">
                                    <option value="">All Subclasses</option>
                                </select>
                            </div>
                            @endif
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-primary w-100" id="applyFiltersBtn">
                                    <i class="bi bi-search"></i> Apply Filters
                                </button>
                            </div>
                        </div>
                        
                        <!-- Statistics -->
                        <div class="row" id="studentsStatistics">
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);">
                                    <div class="card-body text-center">
                                        <h5 class="mb-0" id="statTotalStudents">0</h5>
                                        <small class="text-muted">Total Students</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #cfe2ff 0%, #b6d4fe 100%);">
                                    <div class="card-body text-center">
                                        <h5 class="mb-0" id="statMaleStudents">0</h5>
                                        <small class="text-muted">Male</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f8d7da 0%, #f5c2c7 100%);">
                                    <div class="card-body text-center">
                                        <h5 class="mb-0" id="statFemaleStudents">0</h5>
                                        <small class="text-muted">Female</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);">
                                    <div class="card-body text-center">
                                        <h5 class="mb-0" id="statHealthIssues">0</h5>
                                        <small class="text-muted">Health Issues</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" id="studentsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="active-students-tab" data-toggle="tab" href="#active-students" role="tab" aria-controls="active-students" aria-selected="true">
                            <i class="bi bi-check-circle"></i> Active Students
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="transferred-students-tab" data-toggle="tab" href="#transferred-students" role="tab" aria-controls="transferred-students" aria-selected="false">
                            <i class="bi bi-arrow-right-circle"></i> Transferred Students
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="studentsTabContent">
                    <!-- Active Students Tab -->
                    <div class="tab-pane fade show active" id="active-students" role="tabpanel" aria-labelledby="active-students-tab">
                <div class="table-responsive">
                            <table class="table table-striped table-hover" id="activeStudentsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Photo</th>
                                <th>Admission No.</th>
                                <th>Name</th>
                                <th>Gender</th>
                                @if(isset($isCoordinatorView) && $isCoordinatorView)
                                <th>Subclass</th>
                                @endif
                                <th>Parent</th>
                                <th>Fingerprint Status</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                                    <!-- Active students will be loaded here via AJAX -->
                        </tbody>
                    </table>
                        </div>
                    </div>

                    <!-- Transferred Students Tab -->
                    <div class="tab-pane fade" id="transferred-students" role="tabpanel" aria-labelledby="transferred-students-tab">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="transferredStudentsTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Photo</th>
                                        <th>Admission No.</th>
                                        <th>Name</th>
                                        <th>Gender</th>
                                        @if(isset($isCoordinatorView) && $isCoordinatorView)
                                        <th>Subclass</th>
                                        @endif
                                        <th>Previous Class</th>
                                        <th>Grade/Division</th>
                                        <th>Parent</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Transferred students will be loaded here via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Parents Modal -->
<div class="modal fade" id="viewParentsModal" tabindex="-1" role="dialog" aria-labelledby="viewParentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%; width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewParentsModalLabel">
                    <i class="bi bi-person-badge"></i> Parents/Guardians
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3 text-end">
                    @if(isset($decryptedSubclassID))
                        <button class="btn btn-sm btn-danger" id="downloadParentsPdfBtn">
                            <i class="bi bi-file-pdf"></i> Download PDF
                        </button>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="parentsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Occupation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Parents will be loaded here via AJAX -->
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

<!-- Subject Management Modal -->
<div class="modal fade" id="subjectManagementModal" tabindex="-1" role="dialog" aria-labelledby="subjectManagementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%; width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="subjectManagementModalLabel">
                    <i class="bi bi-book"></i> Subject Management
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="teacherSubjectsContainer">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary-custom" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading subjects...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Subject Election Modal for Teacher -->
<div class="modal fade" id="teacherSubjectElectionModal" tabindex="-1" aria-labelledby="teacherSubjectElectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" style="max-width: 95%; width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="teacherSubjectElectionModalLabel">
                    <i class="bi bi-person-check"></i> <span id="teacherElectionSubjectTitle">Subject Election</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="teacherElectionStudentsContainer">
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
                <button type="button" class="btn btn-primary-custom" id="teacherSaveElectionBtn">
                    <i class="bi bi-save"></i> Save Election
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Student Details Modal - Included from partial blade -->
@include('student_registration.view-student-modal')

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" role="dialog" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editStudentModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Student
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editStudentForm">
                <div class="modal-body" id="editStudentContent">
                    <!-- Edit form will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary-custom">Update Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Parent Details Modal -->
<div class="modal fade" id="viewParentDetailsModal" tabindex="-1" role="dialog" aria-labelledby="viewParentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewParentDetailsModalLabel">
                    <i class="bi bi-person-circle"></i> Parent Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="parentDetailsContent">
                <!-- Parent details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Parent Modal -->
<div class="modal fade" id="editParentModal" tabindex="-1" role="dialog" aria-labelledby="editParentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editParentModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Parent
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editParentForm">
                <div class="modal-body" id="editParentContent">
                    <!-- Edit form will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary-custom">Update Parent</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Shift Student Modal -->
<div class="modal fade" id="shiftStudentModal" tabindex="-1" role="dialog" aria-labelledby="shiftStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="shiftStudentModalLabel">
                    <i class="bi bi-arrow-right-circle"></i> Shift Student
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="shiftStudentForm">
                <div class="modal-body">
                    <input type="hidden" name="studentID" id="shift_studentID">
                    <div class="form-group">
                        <label for="new_subclassID">Select New Class <span class="text-danger">*</span></label>
                        <select class="form-control" id="new_subclassID" name="new_subclassID" required>
                            <option value="">Select Class</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary-custom">Shift Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Results Modal -->
<div class="modal fade" id="viewResultsModal" tabindex="-1" role="dialog" aria-labelledby="viewResultsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewResultsModalLabel">
                    <i class="bi bi-clipboard-data"></i> Student Results
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="resultsModalBody" style="max-height: 80vh; overflow-y: auto;">
                <!-- Search and Select Examination Section -->
                <div class="mb-4">
                    <div class="form-group">
                        <label for="examSearchInput"><i class="bi bi-search"></i> Search Examination:</label>
                        <input type="text" class="form-control" id="examSearchInput" placeholder="Search by exam name, year, or status...">
                </div>
                    <div id="examinationsList" class="mt-3" style="max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.25rem; padding: 10px;">
                    <div class="text-center">
                        <div class="spinner-border text-primary-custom" role="status">
                                <span class="sr-only">Loading examinations...</span>
                        </div>
                        </div>
                    </div>
                </div>

                <!-- Results Section -->
                <div id="resultsContent">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Please select an examination to view results.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View More Results Modal -->
<div class="modal fade" id="viewMoreResultsModal" tabindex="-1" role="dialog" aria-labelledby="viewMoreResultsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewMoreResultsModalLabel">
                    <i class="bi bi-graph-up"></i> Student Detailed Results
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="moreResultsModalBody" style="max-height: 80vh; overflow-y: auto;">
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Management Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog" aria-labelledby="attendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="attendanceModalLabel">
                    <i class="bi bi-calendar-check"></i> Attendance Management
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" id="attendanceTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="collect-attendance-tab" data-toggle="tab" href="#collect-attendance" role="tab" aria-controls="collect-attendance" aria-selected="true">
                            <i class="bi bi-plus-circle"></i> Collect Attendance
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="collected-attendance-tab" data-toggle="tab" href="#collected-attendance" role="tab" aria-controls="collected-attendance" aria-selected="false">
                            <i class="bi bi-list-check"></i> Collected Attendance
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="attendance-overview-tab" data-toggle="tab" href="#attendance-overview" role="tab" aria-controls="attendance-overview" aria-selected="false">
                            <i class="bi bi-bar-chart"></i> Attendance Overview
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="fingerprint-attendance-tab" data-toggle="tab" href="#fingerprint-attendance" role="tab" aria-controls="fingerprint-attendance" aria-selected="false">
                            <i class="bi bi-fingerprint"></i> Fingerprint Attendance
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="live-fingerprint-attendance-tab" data-toggle="tab" href="#live-fingerprint-attendance" role="tab" aria-controls="live-fingerprint-attendance" aria-selected="false">
                            <i class="bi bi-broadcast-pin"></i> Live Attendance (Today)
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="fingerprint-attendance-overview-tab" data-toggle="tab" href="#fingerprint-attendance-overview" role="tab" aria-controls="fingerprint-attendance-overview" aria-selected="false">
                            <i class="bi bi-bar-chart-fill"></i> Fingerprint Attendance Overview
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="attendanceTabContent">
                    <!-- Collect Attendance Tab -->
                    <div class="tab-pane fade show active" id="collect-attendance" role="tabpanel" aria-labelledby="collect-attendance-tab">
                        <form id="collectAttendanceForm">
                            @if(isset($isCoordinatorView) && $isCoordinatorView)
                                <input type="hidden" name="classID" value="{{ $decryptedClassID }}" id="attendanceClassID">
                                <input type="hidden" name="coordinator" value="true">
                                <div class="form-group mb-3">
                                    <label for="attendanceSubclassSelect">Select Subclass <span class="text-danger">*</span></label>
                                    <select class="form-control" id="attendanceSubclassSelect" name="subclassID" required>
                                        <option value="">Select Subclass</option>
                                        <!-- Options will be loaded via AJAX -->
                                    </select>
                                </div>
                            @else
                                <input type="hidden" name="subclassID" value="{{ $decryptedSubclassID }}" id="attendanceSubclassID">
                            @endif
                            <input type="hidden" name="schoolID" value="{{ Session::get('schoolID') }}">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="attendance_date">Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="attendance_date" name="attendance_date" required value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                                        <small class="form-text text-muted"><i class="bi bi-info-circle"></i> Only today's date ({{ date('Y-m-d') }}) is allowed for attendance collection</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Quick Actions</label>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-success" id="markAllPresent">
                                                <i class="bi bi-check-all"></i> Mark All Present
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning" id="markAllAbsent">
                                                <i class="bi bi-x-circle"></i> Mark All Absent
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="bg-primary-custom text-white">
                                        <tr>
                                            <th>#</th>
                                            <th>Photo</th>
                                            <th>Admission No.</th>
                                            <th>Student Name</th>
                                            <th>Status</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody id="attendanceStudentsList">
                                        <!-- Students will be loaded here -->
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 text-end">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary-custom">
                                    <i class="bi bi-save"></i> Save Attendance
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Collected Attendance Tab -->
                    <div class="tab-pane fade" id="collected-attendance" role="tabpanel" aria-labelledby="collected-attendance-tab">
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="searchAttendanceDate">Search by Date</label>
                                        <input type="date" class="form-control" id="searchAttendanceDate">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="searchAttendanceStatus">Filter by Status</label>
                                        <select class="form-control" id="searchAttendanceStatus">
                                            <option value="">All Status</option>
                                            <option value="Present">Present</option>
                                            <option value="Absent">Absent</option>
                                            <option value="Late">Late</option>
                                            <option value="Excused">Excused</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="button" class="btn btn-primary-custom btn-block" id="searchAttendanceBtn">
                                                <i class="bi bi-search"></i> Search
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="collectedAttendanceTable">
                                <thead class="bg-primary-custom text-white">
                                    <tr>
                                        <th>#</th>
                                        <th>Photo</th>
                                        <th>Admission No.</th>
                                        <th>Student Name</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Remark</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="collectedAttendanceList">
                                    <!-- Collected attendance will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Attendance Overview Tab -->
                    <div class="tab-pane fade" id="attendance-overview" role="tabpanel" aria-labelledby="attendance-overview-tab">
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="overviewSearchType">Search Type</label>
                                        <select class="form-control" id="overviewSearchType">
                                            <option value="day">By Day</option>
                                            <option value="week">By Week</option>
                                            <option value="month">By Month</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="overviewSearchDate">Select Date</label>
                                        <input type="date" class="form-control" id="overviewSearchDate" value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="button" class="btn btn-primary-custom btn-block" id="generateOverviewBtn">
                                                <i class="bi bi-search"></i> Generate Overview
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="attendanceOverviewContent">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Please select search type and date to generate attendance overview.
                            </div>
                        </div>

                        <!-- Charts Container -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary-custom text-white">
                                        <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Attendance Chart</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="attendanceChart" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary-custom text-white">
                                        <h6 class="mb-0"><i class="bi bi-pie-chart"></i> Status Distribution</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="statusChart" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Overview Table Container -->
                        <div id="attendanceOverviewTableContainer" class="mt-4"></div>
                    </div>

                    <!-- Fingerprint Attendance Tab (Device) -->
                    <div class="tab-pane fade" id="fingerprint-attendance" role="tabpanel" aria-labelledby="fingerprint-attendance-tab">
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-fingerprint"></i> Fingerprint Attendance from Biometric System
                            </h6>
                            <div class="d-flex align-items-center gap-2">
                                <div>
                                    <label class="form-label mb-1" style="font-size: 0.85rem;">Filter by Date</label>
                                    <input type="date" class="form-control form-control-sm" id="fingerprintAttendanceDateFilter" style="width: 180px;">
                                </div>
                                <button type="button" class="btn btn-sm btn-success mt-3" id="syncAllFingerprintAttendance">
                                    <i class="bi bi-cloud-download"></i> Sync All from Device
                                </button>
                                <button type="button" class="btn btn-sm btn-primary-custom mt-3" id="refreshFingerprintAttendance">
                                    <i class="bi bi-arrow-repeat"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <div id="fingerprintAttendanceContentTeacher">
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> Click <strong>Refresh</strong> to load attendance records from the database. Use <strong>Sync All from Device</strong> to sync latest data from the biometric device.
                            </div>
                        </div>
                    </div>

                    <!-- Live Fingerprint Attendance (Today) Tab -->
                    <div class="tab-pane fade" id="live-fingerprint-attendance" role="tabpanel" aria-labelledby="live-fingerprint-attendance-tab">
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-broadcast-pin"></i> Live Fingerprint Attendance (Today)
                            </h6>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success" id="liveStatusBadge">
                                    <i class="bi bi-circle-fill"></i> Live
                                </span>
                                <button type="button" class="btn btn-sm btn-secondary" id="stopLiveFingerprintAttendance">
                                    <i class="bi bi-stop-circle"></i> Stop
                                </button>
                                <button type="button" class="btn btn-sm btn-primary-custom" id="refreshLiveFingerprintAttendance">
                                    <i class="bi bi-arrow-repeat"></i> Refresh Now
                                </button>
                            </div>
                        </div>
                        <div id="liveFingerprintAttendanceContentTeacher">
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> <strong>Live syncing is active.</strong> Today's attendance is automatically synced from the biometric device every 2 seconds and saved to the database. When a student scans their fingerprint, it appears here immediately.
                            </div>
                        </div>
                    </div>

                    <!-- Fingerprint Attendance Overview Tab -->
                    <div class="tab-pane fade" id="fingerprint-attendance-overview" role="tabpanel" aria-labelledby="fingerprint-attendance-overview-tab">
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="fingerprintOverviewSearchType">Search Type</label>
                                        <select class="form-control" id="fingerprintOverviewSearchType">
                                            <option value="day">By Day</option>
                                            <option value="month" selected>By Month</option>
                                            <option value="year">By Year</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2" id="fingerprintMonthPickerContainer">
                                    <div class="form-group">
                                        <label for="fingerprintOverviewMonth">Select Month</label>
                                        <input type="month" class="form-control" id="fingerprintOverviewMonth" value="{{ date('Y-m') }}">
                                    </div>
                                </div>
                                <div class="col-md-2" id="fingerprintYearPickerContainer" style="display: none;">
                                    <div class="form-group">
                                        <label for="fingerprintOverviewYear">Select Year</label>
                                        <input type="number" class="form-control" id="fingerprintOverviewYear" min="2020" max="{{ date('Y') + 5 }}" value="{{ date('Y') }}">
                                    </div>
                                </div>
                                <div class="col-md-2" id="fingerprintDayPickerContainer" style="display: none;">
                                    <div class="form-group">
                                        <label for="fingerprintOverviewSearchDate">Select Date</label>
                                        <input type="date" class="form-control" id="fingerprintOverviewSearchDate" value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="button" class="btn btn-primary-custom btn-block" id="generateFingerprintOverviewBtn">
                                                <i class="bi bi-search"></i> Generate Overview
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div class="btn-group" role="group" style="width: 100%;">
                                            <button type="button" class="btn btn-success btn-sm" id="exportStudentFingerprintExcelBtn" title="Export to Excel" style="display: inline-block; cursor: pointer;">
                                                <i class="bi bi-file-earmark-excel"></i> Excel
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" id="exportStudentFingerprintPdfBtn" title="Export to PDF" style="display: inline-block; cursor: pointer;">
                                                <i class="bi bi-file-earmark-pdf"></i> PDF
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="fingerprintAttendanceOverviewContent">
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
                                        <canvas id="fingerprintAttendanceChart" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary-custom text-white">
                                        <h6 class="mb-0"><i class="bi bi-pie-chart"></i> Check In/Out Distribution</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="fingerprintStatusChart" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Attendance Modal -->
<div class="modal fade" id="editAttendanceModal" tabindex="-1" role="dialog" aria-labelledby="editAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editAttendanceModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Attendance
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editAttendanceForm">
                <div class="modal-body">
                    <input type="hidden" name="attendanceID" id="edit_attendanceID">
                    <div class="form-group">
                        <label for="edit_attendance_date">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="edit_attendance_date" name="attendance_date" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_attendance_status">Status <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_attendance_status" name="status" required>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                            <option value="Excused">Excused</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_attendance_remark">Remark</label>
                        <textarea class="form-control" id="edit_attendance_remark" name="remark" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary-custom">Update Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('includes.footer')

<!-- Include Student Registration Modal (Steps) -->
@include('student_registration.modal')

<script>
(function($) {
    'use strict';

    if (typeof $ === 'undefined' || typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded!');
        return;
    }

    // Global variables for export functions
    var subclassID = @if(isset($decryptedSubclassID) && $decryptedSubclassID) {{ $decryptedSubclassID }} @else null @endif;
    var classID = @if(isset($decryptedClassID) && $decryptedClassID) {{ $decryptedClassID }} @else null @endif;
    var isCoordinatorView = @if(isset($isCoordinatorView) && $isCoordinatorView) true @else false @endif;
    
    $(document).ready(function() {
        
        // Store all students data globally for statistics and filtering
        var allStudentsData = [];
        
        // Initialize global export data
        window.allStudentsForExport = [];

        // Show/hide allergies details based on checkbox
        $('#tc_has_allergies').on('change', function() {
            if ($(this).is(':checked')) {
                $('#tcAllergiesDetailsContainer').slideDown();
            } else {
                $('#tcAllergiesDetailsContainer').slideUp();
                $('#tc_allergies_details').val('');
            }
        });

        // Register Student Button
        // Register Student Button - Open registration modal (steps) with pre-selected subclass
        $('#registerStudentBtn').on('click', function() {
            if (subclassID) {
                // Get subclass name for display
                let subclassName = '{{ isset($subclassDisplayName) ? $subclassDisplayName : "" }}';
                if (!subclassName) {
                    subclassName = 'Class'; // Fallback
                }
                
                // Set subclassID and name in the registration modal
                const selectedSubclassIDInput = document.getElementById('selectedSubclassID');
                const selectedSubclassNameDisplay = document.getElementById('selectedSubclassName');
                
                if (selectedSubclassIDInput) {
                    selectedSubclassIDInput.value = subclassID;
                }
                if (selectedSubclassNameDisplay) {
                    selectedSubclassNameDisplay.textContent = subclassName.toUpperCase();
                }
                
                // Open registration modal
                setTimeout(function() {
                    const modal = document.getElementById('registrationModal');
                    if (modal) {
                        if (window.bootstrap && typeof bootstrap.Modal === 'function') {
                            const bsModal = new bootstrap.Modal(modal, {
                                backdrop: 'static',
                                keyboard: false
                            });
                            bsModal.show();
                        } else if (window.jQuery && typeof jQuery.fn.modal === 'function') {
                            jQuery(modal).modal('show');
                        } else {
                            // Manual fallback
                            modal.style.display = 'block';
                            modal.classList.add('show');
                            document.body.classList.add('modal-open');
                            const backdrop = document.createElement('div');
                            backdrop.className = 'modal-backdrop fade show';
                            document.body.appendChild(backdrop);
                        }
                        
                        // Focus first input after modal is shown
                        setTimeout(() => {
                            const firstInput = modal.querySelector('input[name="first_name"]');
                            if (firstInput) firstInput.focus();
                        }, 300);
                    }
                }, 100);
            } else {
                Swal.fire('Error', 'Class ID not found', 'error');
            }
        });
        
        // Function to load registration form data with pre-selected subclass
        function loadRegistrationFormData(preSelectSubclassID) {
            // Load subclasses
            $.ajax({
                url: '{{ route("get_subclasses_for_school") }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let subclassSelect = $('#addStudentModal #subclassID');
                        
                        // Destroy existing Select2 if it exists
                        if (subclassSelect.hasClass('select2-hidden-accessible')) {
                            subclassSelect.select2('destroy');
                        }
                        
                        subclassSelect.html('<option value="">Choose a class...</option>');
                        
                        // Filter subclasses if preSelectSubclassID is provided
                        let filteredSubclasses = response.subclasses;
                        if (preSelectSubclassID) {
                            filteredSubclasses = response.subclasses.filter(function(subclass) {
                                return subclass.subclassID == preSelectSubclassID;
                            });
                        }
                        
                        filteredSubclasses.forEach(function(subclass) {
                            const displayName = subclass.display_name || (subclass.class_name + ' ' + subclass.subclass_name) || subclass.subclass_name;
                            subclassSelect.append('<option value="' + subclass.subclassID + '">' + displayName + '</option>');
                        });
                        
                        // Initialize Select2
                        subclassSelect.select2({
                            theme: 'bootstrap-5',
                            placeholder: preSelectSubclassID ? 'Class (Pre-selected)' : 'Search and select a class...',
                            allowClear: !preSelectSubclassID,
                            width: '100%',
                            dropdownParent: $('#addStudentModal')
                        });
                        
                        // Pre-select subclass if provided
                        if (preSelectSubclassID) {
                            subclassSelect.val(preSelectSubclassID).trigger('change');
                            subclassSelect.prop('disabled', true); // Disable selection for teacher
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
                        let parentSelect = $('#addStudentModal #parentID');
                        parentSelect.html('<option value="">Choose a parent...</option>');
                        response.parents.forEach(function(parent) {
                            let fullName = (parent.first_name || '') + ' ' + (parent.middle_name || '') + ' ' + (parent.last_name || '');
                            fullName = fullName.trim().replace(/\s+/g, ' ');
                            let displayText = fullName + (parent.phone ? ' (' + parent.phone + ')' : '');
                            parentSelect.append('<option value="' + parent.parentID + '">' + displayText + '</option>');
                        });
                        
                        if (parentSelect.length) {
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
        
        // Show/hide allergies details based on checkbox (for addStudentModal)
        $(document).on('change', '#addStudentModal #has_allergies', function() {
            if ($(this).is(':checked')) {
                $('#addStudentModal #allergiesDetailsContainer').slideDown();
            } else {
                $('#addStudentModal #allergiesDetailsContainer').slideUp();
                $('#addStudentModal #allergies_details').val('');
            }
        });
        
        // Register Student Form Submission Handler (for addStudentModal)
        $(document).on('submit', '#addStudentForm', function(e) {
            e.preventDefault();

            // Client-side validation
            let first_name = $('#addStudentModal #first_name').val().trim();
            let last_name = $('#addStudentModal #last_name').val().trim();
            let gender = $('#addStudentModal #gender').val();
            let subclassID = $('#addStudentModal #subclassID').val();
            let admission_number = $('#addStudentModal #admission_number').val().trim();

            // Clear previous error messages
            $('#addStudentModal .text-danger.validation-error').remove();
            $('#addStudentModal .form-control, #addStudentModal .form-select').removeClass('is-invalid');

            let hasErrors = false;

            // Validate required fields
            if (!first_name) {
                $('#addStudentModal #first_name').addClass('is-invalid').after('<div class="text-danger validation-error small">First name is required</div>');
                hasErrors = true;
            }

            if (!last_name) {
                $('#addStudentModal #last_name').addClass('is-invalid').after('<div class="text-danger validation-error small">Last name is required</div>');
                hasErrors = true;
            }

            if (!gender) {
                $('#addStudentModal #gender').addClass('is-invalid').after('<div class="text-danger validation-error small">Gender is required</div>');
                hasErrors = true;
            }

            if (!subclassID) {
                $('#addStudentModal #subclassID').addClass('is-invalid').after('<div class="text-danger validation-error small">Class is required</div>');
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
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    // Show loading overlay
                    $('body').append('<div id="formLoadingOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; align-items: center; justify-content: center;"><div style="background: white; padding: 30px; border-radius: 10px; text-align: center;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3">Registering student...</p></div></div>');
                },
                success: function(response) {
                    $('#formLoadingOverlay').remove();
                    submitBtn.prop('disabled', false).html(originalBtnText);

                    if (response.success) {
                        // Close the registration modal first
                        if ($('#addStudentModal #parentID').hasClass('select2-hidden-accessible')) {
                            $('#addStudentModal #parentID').select2('destroy');
                        }
                        if ($('#addStudentModal #subclassID').hasClass('select2-hidden-accessible')) {
                            $('#addStudentModal #subclassID').select2('destroy');
                        }
                        
                        // Close modal using Bootstrap
                        const modalElement = document.getElementById('addStudentModal');
                        if (modalElement) {
                            if (window.bootstrap && typeof bootstrap.Modal !== 'undefined') {
                                const bsModal = bootstrap.Modal.getInstance(modalElement);
                                if (bsModal) {
                                    bsModal.hide();
                                }
                            } else if (window.jQuery && typeof jQuery.fn.modal !== 'undefined') {
                                jQuery(modalElement).modal('hide');
                            }
                        }
                        
                        $('#addStudentForm')[0].reset();
                        $('#addStudentModal .is-invalid').removeClass('is-invalid');
                        $('#addStudentModal .validation-error').remove();

                        // Show success message with fingerprintID
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
                            // Reload page or refresh students list
                            location.reload();
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
                        $('#addStudentModal #admission_number').addClass('is-invalid').after('<div class="text-danger validation-error small">' + xhr.responseJSON.errors.admission_number[0] + '</div>');
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

        // Register Parent Button
        $('#registerParentBtn').on('click', function() {
            $('#registerParentModal').modal('show');
        });

        // View Students Button
        $('#viewStudentsBtn').on('click', function() {
            if (isCoordinatorView && !classID) {
                Swal.fire('Error', 'Class ID not found', 'error');
                return;
            }
            if (!isCoordinatorView && !subclassID) {
                Swal.fire('Error', 'Class ID not found', 'error');
                return;
            }
            $('#viewStudentsModal').modal('show');
            // Load filters first
            loadFilterOptions();
            // Then load students
            loadStudents();
        });

        // Reinitialize DataTables when tab is switched
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href");
            if (target === '#active-students') {
                if ($.fn.DataTable.isDataTable('#activeStudentsTable')) {
                    $('#activeStudentsTable').DataTable().columns.adjust().responsive.recalc();
                }
            } else if (target === '#transferred-students') {
                if ($.fn.DataTable.isDataTable('#transferredStudentsTable')) {
                    $('#transferredStudentsTable').DataTable().columns.adjust().responsive.recalc();
                }
            }
        });

        // View Parents Button
        $('#viewParentsBtn').on('click', function() {
            $('#viewParentsModal').modal('show');
            loadParents();
        });

        // View Results Button - Redirect to manageResults
        $('#viewResultsBtn').on('click', function() {
            if (isCoordinatorView && classID) {
                // Coordinator view - redirect with classID, default main class will be pre-selected
                // Coordinator can only choose subclass to view results
                window.location.href = '{{ route("manageResults") }}?classID=' + classID + '&coordinator=true';
            } else if (subclassID) {
                // Class teacher view - redirect with subclassID parameter
                window.location.href = '{{ route("manageResults") }}?subclassID=' + subclassID;
            } else {
                Swal.fire('Error', 'Class ID not found', 'error');
                return;
            }
        });

        // Subject Management Button
        $('#subjectManagementBtn').on('click', function() {
            if (isCoordinatorView && classID) {
                // Coordinator view
                $('#subjectManagementModal').modal('show');
                loadTeacherClassSubjects();
            } else if (subclassID) {
                // Class teacher view
                $('#subjectManagementModal').modal('show');
                loadTeacherClassSubjects();
            } else {
                Swal.fire('Error', 'Class ID not found', 'error');
                return;
            }
        });

        // Register Student Form
        $('#registerStudentForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.text-danger').hide().text('');
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            var formData = new FormData(this);
            var submitBtn = $(this).find('button[type="submit"]');
            var originalText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Registering...');

            $.ajax({
                url: '{{ route("save_student") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
                },
                dataType: 'json',
                success: function(response) {
                    submitBtn.prop('disabled', false).html(originalText);
                    if (response.success) {
                        // Close the registration modal first
                        $('#registerStudentModal').modal('hide');
                        $('#registerStudentForm')[0].reset();
                        $('#student_photo').val('');
                        
                        // Simple message – user will proceed to capture fingerprint directly on device
                        if (response.fingerprint_id) {
                            var fid = response.fingerprint_id;
                            Swal.fire({
                                icon: 'success',
                                title: 'Student Registered Successfully',
                                html:
                                    '<div class="text-left">' +
                                        '<p class="mb-2">Student registered successfully.</p>' +
                                        '<p class="mb-1">Please proceed to capture fingerprint of this student in the biometric device with ID ' +
                                            '<strong style="font-size: 1.2rem; color: #940000;">' + fid + '</strong>' +
                                        '.</p>' +
                                        '<hr>' +
                                        '<p class="mb-1"><strong>Steps on the device:</strong></p>' +
                                        '<ol class="mb-2 pl-3">' +
                                            '<li>Open <strong>User Mgt → All Users</strong>.</li>' +
                                            '<li>Type or search this ID: <strong>' + fid + '</strong>.</li>' +
                                            '<li>Click on the student that is displayed.</li>' +
                                            '<li>Select <strong>Edit → Fingerprint</strong>.</li>' +
                                            '<li>Ask the student to place the finger on the device <strong>three times</strong>.</li>' +
                                        '</ol>' +
                                        '<p class="mb-0 text-muted"><strong>Note:</strong> If you do not see this student in the list, please register the user manually in the device with ID <strong>' + fid + '</strong>.</p>' +
                                    '</div>',
                                confirmButtonColor: '#940000',
                                width: 600
                            });
                        } else {
                            Swal.fire('Success', response.message || 'Student registered successfully', 'success');
                        }
                    } else {
                        Swal.fire('Error', response.message || 'Failed to register student', 'error');
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        // Display field-specific errors
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, message) {
                            var input = $('#' + field);
                            if (input.length === 0) {
                                // Try alternative field names
                                if (field === 'admission_number') {
                                    input = $('#admission_number');
                                } else if (field === 'photo') {
                                    input = $('#student_photo');
                                    $('#student_photo_error').text(message).show();
                                } else {
                                    input = $('[name="' + field + '"]');
                                }
                            }
                            if (input.length > 0) {
                                input.addClass('is-invalid');
                                input.after('<div class="invalid-feedback">' + message + '</div>');
                            }
                        });
                        Swal.fire('Validation Error', 'Please check the form for errors', 'error');
                    } else {
                        var errorMsg = 'Network error occurred';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', errorMsg, 'error');
                    }
                }
            });
        });

        // Show Fingerprint Capture Modal
        function showFingerprintCaptureModal(fingerprintId, firstName, sentToDevice, apiResponse) {
            $('#fingerprintIdDisplay').text(fingerprintId);
            $('#fingerprintStudentName').text(firstName);
            
            // Show API response if available
            if (sentToDevice && apiResponse && apiResponse.success) {
                $('#fingerprintSentStatus').show();
                var enrollId = apiResponse.data && apiResponse.data.enroll_id ? apiResponse.data.enroll_id : fingerprintId;
                var registeredAt = apiResponse.data && apiResponse.data.device_registered_at ? apiResponse.data.device_registered_at : '';
                var message = '<div class="text-left">' +
                    '<p class="mb-2"><i class="bi bi-check-circle"></i> <strong>' + (apiResponse.message || 'User registered to biometric device successfully') + '</strong></p>' +
                    '<p class="mb-1"><strong>Fingerprint ID:</strong> ' + fingerprintId + '</p>' +
                    '<p class="mb-1"><strong>Enroll ID:</strong> ' + enrollId + '</p>';
                if (registeredAt) {
                    message += '<p class="mb-0"><strong>Registered At:</strong> ' + registeredAt + '</p>';
                }
                message += '</div>';
                $('#fingerprintSentMessage').html(message);
            } else {
                $('#fingerprintSentStatus').hide();
            }
            
            // Reset progress
            $('#fingerprintProgress').css('width', '0%').attr('aria-valuenow', 0);
            $('#fingerprintProgressText').text('0%');
            $('#fingerprintInstruction').html('<i class="bi bi-hand-index"></i> Place your finger on the device (1st time)');
            $('#fingerprintSuccessMessage').hide();
            
            // Show modal (we now use real-time polling instead of pure simulation)
            $('#fingerprintCaptureModal').modal('show');
        }
        
        // Function to update fingerprint capture progress
        function updateFingerprintProgress(captureCount) {
            const progress = (captureCount / 3) * 100;
            $('#fingerprintProgress').css('width', progress + '%').attr('aria-valuenow', progress);
            $('#fingerprintProgressText').text(Math.round(progress) + '%');
            
            if (captureCount === 1) {
                $('#fingerprintInstruction').html('<i class="bi bi-hand-index"></i> Place your finger on the device (2nd time)');
            } else if (captureCount === 2) {
                $('#fingerprintInstruction').html('<i class="bi bi-hand-index"></i> Place your finger on the device (3rd time - final)');
            } else if (captureCount >= 3) {
                $('#fingerprintInstruction').html('<i class="bi bi-check-circle text-success"></i> Fingerprint capture complete!');
                $('#fingerprintSuccessMessage').show();
                $('#fingerprintProgress').removeClass('progress-bar-animated');
            }
        }
        
        // Poll for fingerprint capture updates (real-time via backend)
        let fingerprintPollInterval = null;
        $('#fingerprintCaptureModal').on('shown.bs.modal', function() {
            const fingerprintId = $('#fingerprintIdDisplay').text();
            let lastCount = 0;
            
            fingerprintPollInterval = setInterval(function() {
                $.ajax({
                    url: '{{ route("check_fingerprint_progress") }}',
                    type: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
                    },
                    data: {
                        fingerprint_id: fingerprintId
                    },
                    success: function(res) {
                        if (!res.success) {
                            return;
                        }
                        const count = res.count || 0;
                        if (count !== lastCount) {
                            lastCount = count;
                            const capped = Math.min(count, 3);
                            updateFingerprintProgress(capped);
                        }
                        if (count >= 3) {
                            clearInterval(fingerprintPollInterval);
                            fingerprintPollInterval = null;
                            // Auto-close modal after short delay
                            setTimeout(function() {
                                $('#fingerprintCaptureModal').modal('hide');
                            }, 2000);
                        }
                    },
                    error: function() {
                        // ignore single errors, keep polling
                    }
                });
            }, 2000);
        });
        
        $('#fingerprintCaptureModal').on('hidden.bs.modal', function() {
            if (fingerprintPollInterval) {
                clearInterval(fingerprintPollInterval);
                fingerprintPollInterval = null;
            }
        });

        // Test Device Connection (AJAX)
        $('#testDeviceConnectionBtn').on('click', function(e) {
            e.preventDefault();
            var deviceIp = $('#device_ip').val().trim();
            var devicePort = $('#device_port').val().trim() || '4370';
            var devicePassword = $('#device_password').val().trim() || '0';

            // Validate inputs
            if (!deviceIp) {
                Swal.fire('Error', 'Please enter device IP address', 'error');
                return false;
            }

            // Validate IP format (basic)
            var ipPattern = /^(\d{1,3}\.){3}\d{1,3}$/;
            if (!ipPattern.test(deviceIp)) {
                Swal.fire('Error', 'Please enter a valid IP address', 'error');
                return false;
            }

            // Show loading state
            var $btn = $(this);
            var originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Testing...');
            
            $('#connectionTestResults').hide();
            $('#connectionTestAlert').removeClass('alert-success alert-danger alert-warning');

            // Make AJAX request
            $.ajax({
                url: '{{ route("test_device_connection") }}',
                method: 'POST',
                data: {
                    ip: deviceIp,
                    port: devicePort,
                    password: devicePassword,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $btn.prop('disabled', false).html(originalHtml);
                    
                    if (response.success) {
                        var message = '<h6 class="alert-heading"><i class="bi bi-check-circle"></i> Connection Successful!</h6>';
                        message += '<hr>';
                        message += '<p class="mb-1"><strong>Device IP:</strong> ' + response.device_info.ip + '</p>';
                        message += '<p class="mb-1"><strong>Port:</strong> ' + response.device_info.port + '</p>';
                        
                        if (response.device_info.serial_number) {
                            message += '<p class="mb-1"><strong>Serial Number:</strong> ' + response.device_info.serial_number + '</p>';
                        }
                        if (response.device_info.firmware_version) {
                            message += '<p class="mb-1"><strong>Firmware Version:</strong> ' + response.device_info.firmware_version + '</p>';
                        }
                        if (response.device_info.device_name) {
                            message += '<p class="mb-1"><strong>Device Name:</strong> ' + response.device_info.device_name + '</p>';
                        }
                        
                        if (response.connection_method) {
                            message += '<p class="mb-0"><small><strong>Connection Method:</strong> ' + response.connection_method + '</small></p>';
                        }
                        
                        $('#connectionTestAlert').addClass('alert-success').html(message);
                        $('#connectionTestResults').show();
                    } else {
                        var message = '<h6 class="alert-heading"><i class="bi bi-x-circle"></i> Connection Failed</h6>';
                        message += '<hr>';
                        message += '<p class="mb-0">' + (response.message || 'Failed to connect to device. Please check your settings.') + '</p>';
                        
                        $('#connectionTestAlert').addClass('alert-danger').html(message);
                        $('#connectionTestResults').show();
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(originalHtml);
                    
                    var message = '<h6 class="alert-heading"><i class="bi bi-x-circle"></i> Connection Error</h6>';
                    message += '<hr>';
                    message += '<p class="mb-0">' + (xhr.responseJSON?.message || 'An error occurred while testing connection.') + '</p>';
                    
                    $('#connectionTestAlert').addClass('alert-danger').html(message);
                    $('#connectionTestResults').show();
                }
            });
        });

        // Retrieve Users from Device
        $('#retrieveUsersBtn').on('click', function(e) {
            e.preventDefault();
            var deviceIp = $('#retrieve_device_ip').val().trim();
            var devicePort = $('#retrieve_device_port').val().trim() || '4370';
            var devicePassword = $('#retrieve_device_password').val().trim() || '0';

            // Validate inputs
            if (!deviceIp) {
                Swal.fire('Error', 'Please enter device IP address', 'error');
                return false;
            }

            // Validate IP format (basic)
            var ipPattern = /^(\d{1,3}\.){3}\d{1,3}$/;
            if (!ipPattern.test(deviceIp)) {
                Swal.fire('Error', 'Please enter a valid IP address', 'error');
                return false;
            }

            // Show loading state
            var $btn = $(this);
            var originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Retrieving...');
            
            $('#retrieveUsersResults').hide();
            $('#retrieveUsersAlert').removeClass('alert-success alert-danger alert-warning');
            $('#retrievedUsersTable').hide();
            $('#retrievedUsersList').empty();

            // Make AJAX request
            $.ajax({
                url: '{{ route("retrieve_users_from_device") }}',
                method: 'POST',
                data: {
                    ip: deviceIp,
                    port: devicePort,
                    password: devicePassword,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $btn.prop('disabled', false).html(originalHtml);
                    
                    if (response.success) {
                        var message = '<h6 class="alert-heading"><i class="bi bi-check-circle"></i> Users Retrieved Successfully!</h6>';
                        message += '<hr>';
                        message += '<p class="mb-1"><strong>Total Users:</strong> ' + response.count + '</p>';
                        
                        $('#retrieveUsersAlert').addClass('alert-success').html(message);
                        $('#retrieveUsersResults').show();
                        
                        // Display users in table
                        if (response.users && response.users.length > 0) {
                            var tableRows = '';
                            response.users.forEach(function(user) {
                                var privilegeText = user.privilege == 14 ? 'Admin' : 'User';
                                tableRows += '<tr>';
                                tableRows += '<td>' + user.uid + '</td>';
                                tableRows += '<td>' + (user.name || 'N/A') + '</td>';
                                tableRows += '<td>' + privilegeText + '</td>';
                                tableRows += '<td>' + (user.user_id || 'N/A') + '</td>';
                                tableRows += '</tr>';
                            });
                            $('#retrievedUsersList').html(tableRows);
                            $('#retrievedUsersTable').show();
                        } else {
                            $('#retrievedUsersList').html('<tr><td colspan="4" class="text-center">No users found on device</td></tr>');
                            $('#retrievedUsersTable').show();
                        }
                    } else {
                        var message = '<h6 class="alert-heading"><i class="bi bi-x-circle"></i> Failed to Retrieve Users</h6>';
                        message += '<hr>';
                        message += '<p class="mb-0">' + (response.message || 'Failed to retrieve users from device. Please check your settings.') + '</p>';
                        
                        $('#retrieveUsersAlert').addClass('alert-danger').html(message);
                        $('#retrieveUsersResults').show();
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(originalHtml);
                    
                    var errorMessage = 'Failed to retrieve users from device.';
                    var errorDetails = '';
                    
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        if (xhr.responseJSON.error_code) {
                            errorDetails += '<p class="mb-1"><strong>Error Code:</strong> ' + xhr.responseJSON.error_code + '</p>';
                        }
                        
                        if (xhr.responseJSON.error_string) {
                            errorDetails += '<p class="mb-1"><strong>Error:</strong> ' + xhr.responseJSON.error_string + '</p>';
                        }
                        
                        if (xhr.responseJSON.recent_logs && xhr.responseJSON.recent_logs.length > 0) {
                            errorDetails += '<hr><h6 class="mb-2">Recent Log Entries:</h6>';
                            errorDetails += '<div style="max-height: 200px; overflow-y: auto; font-size: 0.85em; background: #f8f9fa; padding: 10px; border-radius: 4px;">';
                            xhr.responseJSON.recent_logs.forEach(function(log) {
                                errorDetails += '<div class="mb-1">' + log.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</div>';
                            });
                            errorDetails += '</div>';
                        }
                    } else if (xhr.status === 0) {
                        errorMessage = 'Network error. Please check your connection.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error. Please check logs for details.';
                    }
                    
                    var message = '<h6 class="alert-heading"><i class="bi bi-x-circle"></i> Error</h6>';
                    message += '<hr>';
                    message += '<p class="mb-2">' + errorMessage + '</p>';
                    message += errorDetails;
                    
                    $('#retrieveUsersAlert').addClass('alert-danger').html(message);
                    $('#retrieveUsersResults').show();
                }
            });
        });

        // Realtime Phone Validation
        // Format: 255 + 6/7 + 8 digits = 12 total (e.g., 255614863345 or 255714863345)
        $('#parent_phone').on('input', function() {
            var phone = $(this).val().replace(/[^0-9]/g, '');
            $(this).val(phone);

            var errorDiv = $('#parent_phone_error');
            if (phone.length > 0) {
                if (!phone.match(/^255[67]\d{8}$/)) {
                    if (phone.length < 12) {
                        errorDiv.text('Phone number must be 12 digits (e.g., 255614863345)').show();
                        $(this).addClass('is-invalid');
                    } else if (!phone.startsWith('255')) {
                        errorDiv.text('Phone number must start with 255').show();
                        $(this).addClass('is-invalid');
                    } else if (!phone.match(/^255[67]/)) {
                        errorDiv.text('After 255, must start with 6 or 7').show();
                        $(this).addClass('is-invalid');
                    } else if (phone.length > 12) {
                        errorDiv.text('Phone number must be exactly 12 digits').show();
                        $(this).addClass('is-invalid');
                    } else {
                        errorDiv.text('Invalid format. Use: 255614863345 or 255714863345').show();
                        $(this).addClass('is-invalid');
                    }
                } else {
                    errorDiv.hide();
                    $(this).removeClass('is-invalid');
                }
            } else {
                errorDiv.hide();
                $(this).removeClass('is-invalid');
            }
        });

        // Realtime Email Validation
        $('#parent_email').on('blur', function() {
            var email = $(this).val();
            var errorDiv = $('#parent_email_error');

            if (email.length > 0) {
                var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                if (!emailPattern.test(email)) {
                    errorDiv.text('Please enter a valid email address').show();
                    $(this).addClass('is-invalid');
                } else {
                    errorDiv.hide();
                    $(this).removeClass('is-invalid');
                }
            } else {
                errorDiv.hide();
                $(this).removeClass('is-invalid');
            }
        });

        // Register Parent Form
        $('#registerParentForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.text-danger').hide().text('');
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            // Validate phone before submit
            // Format: 255 + 6/7 + 8 digits = 12 total (e.g., 255614863345 or 255714863345)
            var phone = $('#parent_phone').val().replace(/[^0-9]/g, '');
            if (!phone.match(/^255[67]\d{8}$/)) {
                $('#parent_phone_error').text('Phone number must be 12 digits: 255 + 6 or 7 + 8 more digits (e.g., 255614863345 or 255714863345)').show();
                $('#parent_phone').addClass('is-invalid');
                Swal.fire('Validation Error', 'Please enter a valid phone number (e.g., 255614863345 or 255714863345)', 'error');
                return false;
            }

            // Validate email if provided
            var email = $('#parent_email').val();
            if (email.length > 0) {
                var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                if (!emailPattern.test(email)) {
                    $('#parent_email_error').text('Please enter a valid email address').show();
                    $('#parent_email').addClass('is-invalid');
                    Swal.fire('Validation Error', 'Please enter a valid email address', 'error');
                    return false;
                }
            }

            var formData = new FormData(this);
            var submitBtn = $(this).find('button[type="submit"]');
            var originalText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Registering...');

            $.ajax({
                url: '{{ route("save_parent") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
                },
                dataType: 'json',
                success: function(response) {
                    submitBtn.prop('disabled', false).html(originalText);
                    if (response.success) {
                        Swal.fire('Success', response.message || 'Parent registered successfully', 'success')
                            .then(function() {
                                $('#registerParentModal').modal('hide');
                                $('#registerParentForm')[0].reset();
                                $('#parent_photo').val('');
                                loadParentsDropdown();
                            });
                    } else {
                        Swal.fire('Error', response.message || 'Failed to register parent', 'error');
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        // Display field-specific errors
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, message) {
                            var input = $('#' + field);
                            if (input.length === 0) {
                                // Try alternative field names
                                if (field === 'phone') {
                                    input = $('#parent_phone');
                                } else if (field === 'email') {
                                    input = $('#parent_email');
                                } else if (field === 'photo') {
                                    input = $('#parent_photo');
                                    $('#parent_photo_error').text(message).show();
                                } else {
                                    input = $('[name="' + field + '"]');
                                }
                            }
                            if (input.length > 0) {
                                input.addClass('is-invalid');
                                input.after('<div class="invalid-feedback">' + message + '</div>');
                            }
                        });
                        Swal.fire('Validation Error', 'Please check the form for errors', 'error');
                    } else {
                        var errorMsg = 'Network error occurred';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', errorMsg, 'error');
                    }
                }
            });
        });

        // Load Subclasses for Coordinator
        function loadSubclassesForCoordinator(classID) {
            if (!classID) return;
            
            $.ajax({
                url: '{{ url("get_class_subclasses") }}/' + classID,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var $subclassSelect = $('#subclassID');
                    $subclassSelect.empty();
                    $subclassSelect.append('<option value="">Select Subclass</option>');
                    
                    if (response.subclasses && response.subclasses.length > 0) {
                        $.each(response.subclasses, function(index, subclass) {
                            var displayName = subclass.subclass_name 
                                ? subclass.class_name + ' ' + subclass.subclass_name 
                                : subclass.class_name;
                            $subclassSelect.append('<option value="' + subclass.subclassID + '">' + displayName + '</option>');
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading subclasses:', error);
                    Swal.fire('Error', 'Failed to load subclasses', 'error');
                }
            });
        }

        // Load Filter Options
        function loadFilterOptions() {
            // Load subclasses for coordinator view only
            if (isCoordinatorView && classID) {
                var $subclassSelect = $('#filterSubclass');
                if ($subclassSelect.length > 0) {
                    $.ajax({
                        url: '{{ url("get_class_subclasses") }}/' + classID,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            $subclassSelect.empty();
                            $subclassSelect.append('<option value="">All Subclasses</option>');
                            
                            if (response.subclasses && response.subclasses.length > 0) {
                                $.each(response.subclasses, function(index, subclass) {
                                    var displayName = subclass.subclass_name 
                                        ? subclass.class_name + ' - ' + subclass.subclass_name 
                                        : subclass.class_name;
                                    $subclassSelect.append('<option value="' + subclass.subclassID + '">' + displayName + '</option>');
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading subclasses for filter:', error);
                        }
                    });
                }
            }
        }

        // Calculate and Update Statistics (from all students data, not just visible rows)
        function updateStatistics(filteredData) {
            // Use filtered data if provided, otherwise use all students data
            var studentsToCount = filteredData || allStudentsData;
            
            // Calculate statistics from all students (not just visible in table)
            var totalStudents = studentsToCount.length;
            var maleCount = studentsToCount.filter(function(s) { return s.gender === 'Male'; }).length;
            var femaleCount = studentsToCount.filter(function(s) { return s.gender === 'Female'; }).length;
            var healthIssuesCount = studentsToCount.filter(function(s) { return s.has_health_issues === true || s.has_health_issues === 'true'; }).length;
            
            // Update statistics display
            $('#statTotalStudents').text(totalStudents);
            $('#statMaleStudents').text(maleCount);
            $('#statFemaleStudents').text(femaleCount);
            $('#statHealthIssues').text(healthIssuesCount);
        }

        // Apply Filters and Update Statistics
        function applyFiltersAndUpdateStats() {
            console.log('=== APPLYING FILTERS ===');
            console.log('allStudentsData:', allStudentsData);
            console.log('allStudentsData length:', allStudentsData ? allStudentsData.length : 0);
            
            if (!allStudentsData || allStudentsData.length === 0) {
                console.error('ERROR: No students data available!');
                Swal.fire('Info', 'Please wait for students to load first', 'info');
                return;
            }
            
            var genderFilter = $('#filterGender').val() || '';
            var subclassFilter = $('#filterSubclass').val() || '';
            
            console.log('Filter values:', {
                gender: genderFilter,
                subclass: subclassFilter,
                totalStudents: allStudentsData.length
            });
            
            // Filter all students data
            var filteredStudents = allStudentsData.filter(function(student, index) {
                var matches = true;
                var debugInfo = {
                    index: index,
                    admission_number: student.admission_number,
                    matches: true,
                    reasons: []
                };
                
                // Gender filter (only for coordinator view)
                if (genderFilter) {
                    console.log('Checking gender - Student:', student.admission_number, '- Gender:', student.gender, '- Filter:', genderFilter);
                    if (student.gender !== genderFilter) {
                        matches = false;
                        debugInfo.reasons.push('Gender mismatch: ' + student.gender + ' != ' + genderFilter);
                    }
                }
                
                // Subclass filter (only for coordinator view)
                if (subclassFilter) {
                    console.log('Checking subclass - Student:', student.admission_number, '- SubclassID:', student.subclassID, '- Filter:', subclassFilter);
                    if (!student.subclassID) {
                        matches = false;
                        debugInfo.reasons.push('No subclassID');
                    } else if (student.subclassID.toString() !== subclassFilter) {
                        matches = false;
                        debugInfo.reasons.push('Subclass mismatch: ' + student.subclassID + ' != ' + subclassFilter);
                    }
                }
                
                debugInfo.matches = matches;
                if (!matches && debugInfo.reasons.length > 0) {
                    console.log('Student filtered out:', debugInfo);
                }
                
                return matches;
            });
            
            console.log('=== FILTERING RESULTS ===');
            console.log('Total students:', allStudentsData.length);
            console.log('Filtered students:', filteredStudents.length);
            console.log('Filtered students data:', filteredStudents);
            
            // Update statistics with filtered data
            updateStatistics(filteredStudents);
            
            // Get DataTable instances
            var activeTable = $('#activeStudentsTable').DataTable();
            var transferredTable = $('#transferredStudentsTable').DataTable();
            
            // Store filtered admission numbers for quick lookup
            var filteredAdmissionNumbers = {};
            filteredStudents.forEach(function(s) {
                // Handle both string and number admission numbers, trim whitespace
                var admNum = s.admission_number ? s.admission_number.toString().trim() : '';
                if (admNum && admNum !== 'N/A') {
                    filteredAdmissionNumbers[admNum] = true;
                }
            });
            
            console.log('=== FILTERED ADMISSION NUMBERS ===');
            console.log('Filtered admission numbers:', filteredAdmissionNumbers);
            console.log('Number of filtered admission numbers:', Object.keys(filteredAdmissionNumbers).length);
            console.log('Sample filtered admission numbers:', Object.keys(filteredAdmissionNumbers).slice(0, 10));
            
            // Also log all students admission numbers for comparison
            console.log('=== ALL STUDENTS ADMISSION NUMBERS (for comparison) ===');
            var allAdmNumbers = allStudentsData.map(function(s) {
                return s.admission_number ? s.admission_number.toString().trim() : 'N/A';
            });
            console.log('Total all admission numbers:', allAdmNumbers.length);
            console.log('Sample all admission numbers:', allAdmNumbers.slice(0, 10));
            
            console.log('=== FILTERED ADMISSION NUMBERS ===');
            console.log('Filtered admission numbers:', filteredAdmissionNumbers);
            console.log('Number of filtered admission numbers:', Object.keys(filteredAdmissionNumbers).length);
            console.log('Sample filtered admission numbers:', Object.keys(filteredAdmissionNumbers).slice(0, 10));
            
            // Also log all students admission numbers for comparison
            console.log('=== ALL STUDENTS ADMISSION NUMBERS (for comparison) ===');
            var allAdmNumbers = allStudentsData.map(function(s) {
                return s.admission_number ? s.admission_number.toString().trim() : 'N/A';
            });
            console.log('Total all admission numbers:', allAdmNumbers.length);
            console.log('Sample all admission numbers:', allAdmNumbers.slice(0, 10));
            
            if (activeTable) {
                console.log('Applying filter to active table...');
                
                // Store reference to filtered data for the filter function (use closure)
                var activeFilterData = filteredAdmissionNumbers;
                
                // Remove any existing custom filters for this table
                if ($.fn.dataTable.ext.search) {
                    var originalExtSearch = $.fn.dataTable.ext.search;
                    $.fn.dataTable.ext.search = originalExtSearch.filter(function(fn) {
                        var fnStr = fn.toString();
                        return !(fnStr.indexOf('activeStudentsTable') !== -1 && fnStr.indexOf('admission_number') !== -1);
                    });
                } else {
                    $.fn.dataTable.ext.search = [];
                }
                
                console.log('Remaining ext.search functions:', $.fn.dataTable.ext.search.length);
                
                // Apply custom filter
                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        if (settings.nTable.id !== 'activeStudentsTable') {
                            return true;
                        }
                        
                        try {
                            var row = activeTable.row(dataIndex).node();
                            if (!row) return false;
                            
                            // Get admission number - it's always in column index 2 (after #, Photo, Admission No.)
                            var admissionNumber = $(row).find('td:eq(2)').text().trim();
                            
                            // Debug: log first few rows to verify column index and matching
                            if (dataIndex < 5) {
                                var allCells = [];
                                $(row).find('td').each(function(i) {
                                    allCells.push('[' + i + ']: "' + $(this).text().trim() + '"');
                                });
                                console.log('Active Row', dataIndex, '- All cells:', allCells.join(', '));
                                console.log('Active Row', dataIndex, '- Admission number from cell 2:', '"' + admissionNumber + '"');
                                console.log('Active Row', dataIndex, '- In filter?', activeFilterData.hasOwnProperty(admissionNumber));
                                if (!activeFilterData.hasOwnProperty(admissionNumber)) {
                                    console.log('Active Row', dataIndex, '- Available filter keys (first 5):', Object.keys(activeFilterData).slice(0, 5));
                                }
                            }
                            
                            var shouldShow = activeFilterData[admissionNumber] === true;
                            
                            if (!shouldShow && dataIndex < 5) {
                                console.log('Hiding row', dataIndex, '- Admission:', '"' + admissionNumber + '"', '- Available keys:', Object.keys(activeFilterData).slice(0, 5));
                            }
                            
                            return shouldShow;
                        } catch(e) {
                            console.error('Error in active table filter:', e, 'at dataIndex:', dataIndex);
                            return true;
                        }
                    }
                );
                
                console.log('Total ext.search functions after push:', $.fn.dataTable.ext.search.length);
                console.log('Drawing active table...');
                
                // Clear existing search and redraw
                activeTable.search('').draw();
                
                console.log('Active table drawn. Visible rows:', activeTable.rows({search: 'applied'}).count());
            } else {
                console.error('ERROR: activeTable is not initialized!');
            }
            
            if (transferredTable) {
                console.log('Applying filter to transferred table...');
                
                // Store reference to filtered data for the filter function (use closure)
                var transferredFilterData = filteredAdmissionNumbers;
                
                // Remove any existing custom filters for this table
                if ($.fn.dataTable.ext.search) {
                    var originalExtSearch = $.fn.dataTable.ext.search;
                    $.fn.dataTable.ext.search = originalExtSearch.filter(function(fn) {
                        var fnStr = fn.toString();
                        return !(fnStr.indexOf('transferredStudentsTable') !== -1 && fnStr.indexOf('admission_number') !== -1);
                    });
                } else {
                    $.fn.dataTable.ext.search = [];
                }
                
                // Apply custom filter
                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        if (settings.nTable.id !== 'transferredStudentsTable') {
                            return true;
                        }
                        
                        try {
                            var row = transferredTable.row(dataIndex).node();
                            if (!row) return false;
                            
                            // Get admission number - it's always in column index 2 (after #, Photo, Admission No.)
                            var admissionNumber = $(row).find('td:eq(2)').text().trim();
                            
                            // Debug: log all cells to verify column index
                            if (dataIndex < 3) {
                                var allCells = [];
                                $(row).find('td').each(function(i) {
                                    allCells.push('[' + i + ']: ' + $(this).text().trim());
                                });
                                console.log('Transferred Row', dataIndex, '- All cells:', allCells);
                                console.log('Transferred Row', dataIndex, '- Admission number from cell 2:', admissionNumber);
                            }
                            
                            var shouldShow = transferredFilterData[admissionNumber] === true;
                            
                            if (!shouldShow) {
                                console.log('Hiding transferred row', dataIndex, 'with admission number:', admissionNumber, '- Available in filter:', transferredFilterData.hasOwnProperty(admissionNumber));
                            } else {
                                console.log('Showing transferred row', dataIndex, 'with admission number:', admissionNumber);
                            }
                            
                            return shouldShow;
                        } catch(e) {
                            console.error('Error in transferred table filter:', e, 'at dataIndex:', dataIndex);
                            return true;
                        }
                    }
                );
                
                console.log('Drawing transferred table...');
                
                // Clear existing search and redraw
                transferredTable.search('').draw();
                
                console.log('Transferred table drawn. Visible rows:', transferredTable.rows({search: 'applied'}).count());
            } else {
                console.log('INFO: transferredTable is not initialized (might be empty)');
            }
            
            console.log('=== FILTERING COMPLETE ===');
        }

        // Apply Filters Button - Use AJAX for server-side filtering
        $(document).on('click', '#applyFiltersBtn', function() {
            if (isCoordinatorView && classID) {
                // Coordinator view - use AJAX filtering
                applyFiltersWithAjax();
            } else {
                // Class teacher view - use client-side filtering for now
                applyFiltersAndUpdateStats();
            }
        });
        
        // Apply Filters with AJAX (for coordinator view)
        function applyFiltersWithAjax() {
            if (!classID) {
                Swal.fire('Error', 'Class ID not found', 'error');
                return;
            }
            
            var genderFilter = $('#filterGender').val() || '';
            var subclassFilter = $('#filterSubclass').val() || '';
            
            console.log('=== APPLYING FILTERS WITH AJAX ===');
            console.log('Filters:', {
                gender: genderFilter,
                subclass: subclassFilter,
                classID: classID
            });
            
            // Show loading
            var $btn = $('#applyFiltersBtn');
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
            
            $.ajax({
                url: '{{ url("get_subclass_students") }}/0',
                type: 'GET',
                data: {
                    classID: classID,
                    coordinator: 'true',
                    subclassFilter: subclassFilter,
                    genderFilter: genderFilter
                },
                dataType: 'json',
                success: function(response) {
                    $btn.prop('disabled', false).html(originalText);
                    
                    console.log('=== AJAX RESPONSE ===');
                    console.log('Response:', response);
                    
                    if (response.success && response.students) {
                        // Update statistics from server
                        // Filter only active students before calculating statistics
                        var activeStudents = response.students.filter(function(s) {
                            var status = s.status || '';
                            return status === 'Active' || status === 'active' || status === '';
                        });
                        
                        // Calculate statistics from active students only
                        var activeTotal = activeStudents.length;
                        var activeMale = activeStudents.filter(function(s) { return s.gender === 'Male'; }).length;
                        var activeFemale = activeStudents.filter(function(s) { return s.gender === 'Female'; }).length;
                        var activeHealthIssues = activeStudents.filter(function(s) { return s.has_health_issues === true || s.has_health_issues === 'true'; }).length;
                        
                        // Update statistics display (only active students)
                        $('#statTotalStudents').text(activeTotal);
                        $('#statMaleStudents').text(activeMale);
                        $('#statFemaleStudents').text(activeFemale);
                        $('#statHealthIssues').text(activeHealthIssues);
                        
                        // Reload students table with filtered data (will filter again for safety)
                        renderStudentsTable(response.students);
                    } else {
                        Swal.fire('Error', response.error || 'Failed to filter students', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    $btn.prop('disabled', false).html(originalText);
                    console.error('AJAX Error:', error);
                    console.error('Response:', xhr.responseText);
                    Swal.fire('Error', 'Failed to filter students: ' + error, 'error');
                }
            });
        }
        
        // Render Students Table (reusable function)
        function renderStudentsTable(students) {
            var activeTbody = $('#activeStudentsTable tbody');
            var transferredTbody = $('#transferredStudentsTable tbody');
            activeTbody.empty();
            transferredTbody.empty();
            
            // Filter only active students for export
            var activeStudentsForExport = students.filter(function(s) {
                return s.status === 'Active' || s.status === 'active' || !s.status;
            });
            
            // Store only active students data globally for export
            allStudentsData = activeStudentsForExport.map(function(s) {
                return {
                    studentID: s.studentID,
                    admission_number: s.admission_number || 'N/A',
                    first_name: s.first_name || '',
                    middle_name: s.middle_name || '',
                    last_name: s.last_name || '',
                    full_name: (s.first_name || '') + ' ' + (s.middle_name || '') + ' ' + (s.last_name || ''),
                    gender: s.gender || 'N/A',
                    date_of_birth: s.date_of_birth || 'N/A',
                    admission_date: s.admission_date || 'N/A',
                    subclassID: s.subclassID,
                    subclass_display: s.subclass_display || (s.class_name && s.subclass_name ? s.class_name + ' - ' + s.subclass_name : (s.subclass_name || 'N/A')),
                    parent_name: s.parent_name || 'Not Assigned',
                    address: s.address || 'N/A',
                    status: s.status || 'Active',
                    has_health_issues: s.has_health_issues || false,
                    photo: s.photo || null
                };
            });
            
            // Store full students data globally for export (only active)
            window.allStudentsForExport = allStudentsData;
            console.log('Active students data stored for export:', window.allStudentsForExport.length, 'students');
            
            // Filter only active students for display (exclude Transferred, Graduated, Inactive)
            var activeStudentsForDisplay = students.filter(function(s) {
                var status = s.status || '';
                return status === 'Active' || status === 'active' || status === '';
            });
            
            // Update statistics with only active students
            if (activeStudentsForDisplay && activeStudentsForDisplay.length > 0) {
                var activeTotal = activeStudentsForDisplay.length;
                var activeMale = activeStudentsForDisplay.filter(function(s) { return s.gender === 'Male'; }).length;
                var activeFemale = activeStudentsForDisplay.filter(function(s) { return s.gender === 'Female'; }).length;
                var activeHealthIssues = activeStudentsForDisplay.filter(function(s) { return s.has_health_issues === true || s.has_health_issues === 'true'; }).length;
                
                // Update statistics display
                $('#statTotalStudents').text(activeTotal);
                $('#statMaleStudents').text(activeMale);
                $('#statFemaleStudents').text(activeFemale);
                $('#statHealthIssues').text(activeHealthIssues);
            } else {
                // Reset statistics if no active students
                $('#statTotalStudents').text(0);
                $('#statMaleStudents').text(0);
                $('#statFemaleStudents').text(0);
                $('#statHealthIssues').text(0);
            }
            
            if (activeStudentsForDisplay && activeStudentsForDisplay.length > 0) {
                var activeIndex = 0;
                var transferredIndex = 0;
                var schoolType = '{{ isset($school_details) ? $school_details->school_type : "Secondary" }}';
                
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
                
                $.each(activeStudentsForDisplay, function(index, student) {
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
                    
                    // Create photo HTML
                    var photoHtml = '';
                    if (photoUrl) {
                        photoHtml = '<div style="position: relative; display: inline-block;">' +
                            '<img src="' + photoUrl + '" alt="Student Photo" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #940000; cursor: pointer;" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">' +
                            '<div class="rounded-circle d-none align-items-center justify-content-center text-white" style="width: 40px; height: 40px; background-color: ' + placeholderColor + '; font-size: 16px; font-weight: bold; border: 2px solid #940000;">' + firstLetter + '</div>' +
                            '</div>';
                    } else {
                        photoHtml = '<div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px; background-color: ' + placeholderColor + '; font-size: 16px; font-weight: bold; border: 2px solid #940000;">' + firstLetter + '</div>';
                    }
                    
                    // Get subclass display
                    var subclassDisplay = student.subclass_display || (student.class_name && student.subclass_name ? student.class_name + ' - ' + student.subclass_name : (student.subclass_name || 'N/A'));
                    
                    var actionButtons = '<button class="btn btn-sm btn-info view-student-btn" data-student-id="' + student.studentID + '" title="View Details"><i class="bi bi-eye"></i></button> ' +
                        '<button class="btn btn-sm btn-warning edit-student-btn" data-student-id="' + student.studentID + '" title="Edit"><i class="bi bi-pencil-square"></i></button> ';
                    
                    // Handle Transferred students
                    if (student.status === 'Transferred') {
                        actionButtons += '<button class="btn btn-sm btn-success activate-student-btn" data-student-id="' + student.studentID + '" title="Activate Student"><i class="bi bi-check-circle"></i></button> ';
                        if (student.old_subclassID && student.old_subclass_info) {
                            actionButtons += '<button class="btn btn-sm btn-secondary revert-transfer-btn" data-student-id="' + student.studentID + '" title="Revert to ' + (student.old_subclass_info.display_name || 'Previous Class') + '"><i class="bi bi-arrow-left-circle"></i></button> ';
                        }
                        actionButtons += '<button class="btn btn-sm btn-danger delete-student-btn" data-student-id="' + student.studentID + '" title="Delete"><i class="bi bi-trash-fill"></i></button>';
                        
                        var gradeDisplay = '<span class="text-muted">N/A</span>';
                        var gradeClass = '';
                        if (student.student_grade) {
                            var grade = student.student_grade;
                            if (grade.startsWith('I.')) {
                                gradeClass = 'bg-success text-white';
                            } else if (grade.startsWith('II.')) {
                                gradeClass = 'bg-info text-white';
                            } else if (grade.startsWith('III.')) {
                                gradeClass = 'bg-warning text-dark';
                            } else if (grade.startsWith('IV.')) {
                                gradeClass = 'bg-danger text-white';
                            } else if (grade.startsWith('0.')) {
                                gradeClass = 'bg-secondary text-white';
                            } else if (grade === 'Division One') {
                                gradeClass = 'bg-success text-white';
                            } else if (grade === 'Division Two') {
                                gradeClass = 'bg-info text-white';
                            } else if (grade === 'Division Three') {
                                gradeClass = 'bg-warning text-dark';
                            } else if (grade === 'Division Four') {
                                gradeClass = 'bg-danger text-white';
                            } else {
                                gradeClass = 'bg-secondary text-white';
                            }
                            gradeDisplay = '<span class="badge ' + gradeClass + '">' + grade + '</span>';
                        }
                        
                        var previousClass = '<span class="text-muted">N/A</span>';
                        if (student.old_subclass_info) {
                            previousClass = '<strong>' + (student.old_subclass_info.subclass_name || student.old_subclass_info.display_name || 'N/A') + '</strong>';
                        }
                        
                        var subclassIDForFilter = student.subclassID || '';
                        var row = $('<tr>')
                            .attr('data-subclass-id', subclassIDForFilter)
                            .attr('data-admission-date', student.admission_date || '')
                            .attr('data-has-health-issues', student.has_health_issues || false)
                            .html(
                                '<td>' + (transferredIndex + 1) + '</td>' +
                                '<td>' + photoHtml + '</td>' +
                                '<td>' + (student.admission_number || 'N/A') + '</td>' +
                                '<td>' + (student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '') + '</td>' +
                                '<td>' + (student.gender || 'N/A') + '</td>' +
                                '<td>' + subclassDisplay + '</td>' +
                                '<td>' + previousClass + '</td>' +
                                '<td>' + gradeDisplay + '</td>' +
                                '<td>' + (student.parent_name || 'Not Assigned') + '</td>' +
                                '<td>' + actionButtons + '</td>'
                            );
                        transferredTbody.append(row);
                        transferredIndex++;
                    } else {
                        // Active Students
                        actionButtons += '<button class="btn btn-sm btn-primary shift-student-btn" data-student-id="' + student.studentID + '" title="Shift Class"><i class="bi bi-arrow-right-circle"></i></button> ';
                        actionButtons += '<a href="#" class="btn btn-sm btn-success text-white send-student-to-fingerprint-btn" data-student-id="' + student.studentID + '" data-student-name="' + (student.first_name || '') + '" data-fingerprint-id="' + (student.fingerprint_id || '') + '" title="Send to Fingerprint Device"><i class="bi bi-fingerprint"></i></a> ';
                        
                        if (student.status !== 'Active') {
                            actionButtons += '<button class="btn btn-sm btn-danger delete-student-btn" data-student-id="' + student.studentID + '" title="Delete"><i class="bi bi-trash-fill"></i></button>';
                        }
                        
                        var statusBadge = '<span class="badge badge-success">' + (student.status || 'Active') + '</span>';
                        
                        var fingerprintStatus = '';
                        if (student.fingerprint_id) {
                            if (student.sent_to_device) {
                                var captureCount = student.fingerprint_capture_count || 0;
                                if (captureCount >= 3) {
                                    fingerprintStatus = '<span class="badge badge-success"><i class="bi bi-check-circle"></i> Captured (ID: ' + student.fingerprint_id + ')</span>';
                                } else if (captureCount > 0) {
                                    fingerprintStatus = '<span class="badge badge-warning"><i class="bi bi-hourglass-split"></i> In Progress (' + captureCount + '/3) - ID: ' + student.fingerprint_id + '</span>';
                                } else {
                                    fingerprintStatus = '<span class="badge badge-success"><i class="bi bi-check-circle"></i> Sent (ID: ' + student.fingerprint_id + ')</span>';
                                }
                            } else {
                                fingerprintStatus = '<span class="badge badge-success"><i class="bi bi-check-circle"></i> Sent (ID: ' + student.fingerprint_id + ')</span>';
                            }
                        } else {
                            fingerprintStatus = '<span class="badge badge-secondary"><i class="bi bi-dash"></i> No ID</span>';
                        }
                        
                        var subclassIDForFilter = student.subclassID || '';
                        var row = $('<tr>')
                            .attr('data-subclass-id', subclassIDForFilter)
                            .attr('data-admission-date', student.admission_date || '')
                            .attr('data-has-health-issues', student.has_health_issues || false)
                            .html(
                                '<td>' + (activeIndex + 1) + '</td>' +
                                '<td>' + photoHtml + '</td>' +
                                '<td>' + (student.admission_number || 'N/A') + '</td>' +
                                '<td>' + (student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '') + '</td>' +
                                '<td>' + (student.gender || 'N/A') + '</td>' +
                                '<td>' + subclassDisplay + '</td>' +
                                '<td>' + (student.parent_name || 'Not Assigned') + '</td>' +
                                '<td>' + fingerprintStatus + '</td>' +
                                '<td>' + statusBadge + '</td>' +
                                '<td><div class="d-flex justify-content-center align-items-center gap-2">' + actionButtons + '</div></td>'
                            );
                        activeTbody.append(row);
                        activeIndex++;
                    }
                });
                
                // Reinitialize DataTables
                if ($.fn.DataTable.isDataTable('#activeStudentsTable')) {
                    $('#activeStudentsTable').DataTable().destroy();
                }
                if ($.fn.DataTable.isDataTable('#transferredStudentsTable')) {
                    $('#transferredStudentsTable').DataTable().destroy();
                }
                
                $('#activeStudentsTable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    order: [[3, 'asc']]
                });
                
                $('#transferredStudentsTable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    order: [[3, 'asc']]
                });
            } else {
                window.allStudentsForExport = [];
                activeTbody.append('<tr><td colspan="10" class="text-center">No students found</td></tr>');
                transferredTbody.append('<tr><td colspan="10" class="text-center">No transferred students found</td></tr>');
            }
        }

        // Load Students for Coordinator (all subclasses in main class)
        function loadStudentsForCoordinator(classID) {
            if (!classID) return;
            
            $.ajax({
                url: '{{ url("get_subclass_students") }}/0?classID=' + classID + '&coordinator=true',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.students) {
                        // Filter only active students before calculating statistics
                        var activeStudents = response.students.filter(function(s) {
                            var status = s.status || '';
                            return status === 'Active' || status === 'active' || status === '';
                        });
                        
                        // Calculate statistics from active students only
                        var activeTotal = activeStudents.length;
                        var activeMale = activeStudents.filter(function(s) { return s.gender === 'Male'; }).length;
                        var activeFemale = activeStudents.filter(function(s) { return s.gender === 'Female'; }).length;
                        var activeHealthIssues = activeStudents.filter(function(s) { return s.has_health_issues === true || s.has_health_issues === 'true'; }).length;
                        
                        // Update statistics display (only active students)
                        $('#statTotalStudents').text(activeTotal);
                        $('#statMaleStudents').text(activeMale);
                        $('#statFemaleStudents').text(activeFemale);
                        $('#statHealthIssues').text(activeHealthIssues);
                        
                        // Use renderStudentsTable to display students (will filter again for safety)
                        renderStudentsTable(response.students);
                        
                        var activeIndex = 0;
                        var transferredIndex = 0;
                        var schoolType = '{{ isset($school_details) ? $school_details->school_type : "Secondary" }}';

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

                        $.each(response.students, function(index, student) {
                            var baseUrl = '{{ asset("") }}';
                            var photoUrl = '';

                            // Get first letter of student name
                            var firstName = student.first_name || '';
                            var firstLetter = firstName ? firstName.charAt(0).toUpperCase() : 'N';

                            // Generate color based on student name
                            var studentFullName = (student.first_name || '') + ' ' + (student.last_name || '');
                            var placeholderColor = getColorFromName(studentFullName);

                            // Determine photo URL - path is public/userImages
                            if (student.photo) {
                                photoUrl = baseUrl + 'userImages/' + student.photo;
                            } else {
                                photoUrl = '';
                            }

                            // Create photo HTML with proper error handling and placeholder
                            var photoHtml = '';
                            if (photoUrl) {
                                photoHtml = '<div style="position: relative; display: inline-block;">' +
                                    '<img src="' + photoUrl + '" alt="Student Photo" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #940000; cursor: pointer;" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">' +
                                    '<div class="rounded-circle d-none align-items-center justify-content-center text-white" style="width: 40px; height: 40px; background-color: ' + placeholderColor + '; font-size: 16px; font-weight: bold; border: 2px solid #940000;">' + firstLetter + '</div>' +
                                    '</div>';
                            } else {
                                photoHtml = '<div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px; background-color: ' + placeholderColor + '; font-size: 16px; font-weight: bold; border: 2px solid #940000;">' + firstLetter + '</div>';
                            }

                            // Get subclass display (mainclass-subclass format)
                            var subclassDisplay = student.subclass_display || (student.class_name && student.subclass_name ? student.class_name + ' - ' + student.subclass_name : (student.subclass_name || 'N/A'));

                            var actionButtons = '<button class="btn btn-sm btn-info view-student-btn" data-student-id="' + student.studentID + '" title="View Details"><i class="bi bi-eye"></i></button> ' +
                                '<button class="btn btn-sm btn-warning edit-student-btn" data-student-id="' + student.studentID + '" title="Edit"><i class="bi bi-pencil-square"></i></button> ';

                            // Only show in Transferred tab if status is explicitly "Transferred"
                            if (student.status === 'Transferred') {
                                actionButtons += '<button class="btn btn-sm btn-success activate-student-btn" data-student-id="' + student.studentID + '" title="Activate Student"><i class="bi bi-check-circle"></i></button> ';
                                if (student.old_subclassID && student.old_subclass_info) {
                                    actionButtons += '<button class="btn btn-sm btn-secondary revert-transfer-btn" data-student-id="' + student.studentID + '" title="Revert to ' + (student.old_subclass_info.display_name || 'Previous Class') + '"><i class="bi bi-arrow-left-circle"></i></button> ';
                                }
                                actionButtons += '<button class="btn btn-sm btn-danger delete-student-btn" data-student-id="' + student.studentID + '" title="Delete"><i class="bi bi-trash-fill"></i></button>';

                                var gradeDisplay = '<span class="text-muted">N/A</span>';
                                var gradeClass = '';
                                if (student.student_grade) {
                                    var grade = student.student_grade;
                                    if (grade.startsWith('I.')) {
                                        gradeClass = 'bg-success text-white';
                                    } else if (grade.startsWith('II.')) {
                                        gradeClass = 'bg-info text-white';
                                    } else if (grade.startsWith('III.')) {
                                        gradeClass = 'bg-warning text-dark';
                                    } else if (grade.startsWith('IV.')) {
                                        gradeClass = 'bg-danger text-white';
                                    } else if (grade.startsWith('0.')) {
                                        gradeClass = 'bg-secondary text-white';
                                    } else if (grade === 'Division One') {
                                        gradeClass = 'bg-success text-white';
                                    } else if (grade === 'Division Two') {
                                        gradeClass = 'bg-info text-white';
                                    } else if (grade === 'Division Three') {
                                        gradeClass = 'bg-warning text-dark';
                                    } else if (grade === 'Division Four') {
                                        gradeClass = 'bg-danger text-white';
                                    } else {
                                        gradeClass = 'bg-secondary text-white';
                                    }
                                    gradeDisplay = '<span class="badge ' + gradeClass + '">' + grade + '</span>';
                                }

                                var previousClass = '<span class="text-muted">N/A</span>';
                                if (student.old_subclass_info) {
                                    previousClass = '<strong>' + (student.old_subclass_info.subclass_name || student.old_subclass_info.display_name || 'N/A') + '</strong>';
                                }

                                var row = '<tr>' +
                                    '<td>' + (transferredIndex + 1) + '</td>' +
                                    '<td>' + photoHtml + '</td>' +
                                    '<td>' + (student.admission_number || 'N/A') + '</td>' +
                                    '<td>' + (student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '') + '</td>' +
                                    '<td>' + (student.gender || 'N/A') + '</td>' +
                                    '<td>' + subclassDisplay + '</td>' +
                                    '<td>' + previousClass + '</td>' +
                                    '<td>' + gradeDisplay + '</td>' +
                                    '<td>' + (student.parent_name || 'Not Assigned') + '</td>' +
                                    '<td>' + actionButtons + '</td>' +
                                '</tr>';
                                transferredTbody.append(row);
                                transferredIndex++;
                            } else {
                                // Active Students
                                actionButtons += '<button class="btn btn-sm btn-primary shift-student-btn" data-student-id="' + student.studentID + '" title="Shift Class"><i class="bi bi-arrow-right-circle"></i></button> ';
                                actionButtons += '<a href="#" class="btn btn-sm btn-success text-white send-student-to-fingerprint-btn" data-student-id="' + student.studentID + '" data-student-name="' + (student.first_name || '') + '" data-fingerprint-id="' + (student.fingerprint_id || '') + '" title="Send to Fingerprint Device"><i class="bi bi-fingerprint"></i></a> ';

                                if (student.status !== 'Active') {
                                    actionButtons += '<button class="btn btn-sm btn-danger delete-student-btn" data-student-id="' + student.studentID + '" title="Delete"><i class="bi bi-trash-fill"></i></button>';
                                }

                                var statusBadge = '<span class="badge badge-success">' + (student.status || 'Active') + '</span>';

                                var fingerprintStatus = '';
                                if (student.fingerprint_id) {
                                    if (student.sent_to_device) {
                                        var captureCount = student.fingerprint_capture_count || 0;
                                        if (captureCount >= 3) {
                                            fingerprintStatus = '<span class="badge badge-success"><i class="bi bi-check-circle"></i> Captured (ID: ' + student.fingerprint_id + ')</span>';
                                        } else if (captureCount > 0) {
                                            fingerprintStatus = '<span class="badge badge-warning"><i class="bi bi-hourglass-split"></i> In Progress (' + captureCount + '/3) - ID: ' + student.fingerprint_id + '</span>';
                                        } else {
                                            fingerprintStatus = '<span class="badge badge-success"><i class="bi bi-check-circle"></i> Sent (ID: ' + student.fingerprint_id + ')</span>';
                                        }
                                    } else {
                                        fingerprintStatus = '<span class="badge badge-success"><i class="bi bi-check-circle"></i> Sent (ID: ' + student.fingerprint_id + ')</span>';
                                    }
                                } else {
                                    fingerprintStatus = '<span class="badge badge-secondary"><i class="bi bi-dash"></i> No ID</span>';
                                }

                                // Get subclassID for filtering
                                var subclassIDForFilter = student.subclassID || '';
                                
                                var row = $('<tr>')
                                    .attr('data-subclass-id', subclassIDForFilter)
                                    .attr('data-admission-date', student.admission_date || '')
                                    .attr('data-has-health-issues', student.has_health_issues || false)
                                    .html(
                                        '<td>' + (activeIndex + 1) + '</td>' +
                                        '<td>' + photoHtml + '</td>' +
                                        '<td>' + (student.admission_number || 'N/A') + '</td>' +
                                        '<td>' + (student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '') + '</td>' +
                                        '<td>' + (student.gender || 'N/A') + '</td>' +
                                        '<td>' + subclassDisplay + '</td>' +
                                        '<td>' + (student.parent_name || 'Not Assigned') + '</td>' +
                                        '<td>' + fingerprintStatus + '</td>' +
                                        '<td>' + statusBadge + '</td>' +
                                        '<td><div class="d-flex justify-content-center align-items-center gap-2">' + actionButtons + '</div></td>'
                                    );
                                activeTbody.append(row);
                                activeIndex++;
                            }
                        });

                        // Initialize DataTables
                        if ($.fn.DataTable.isDataTable('#activeStudentsTable')) {
                            $('#activeStudentsTable').DataTable().destroy();
                        }
                        if ($.fn.DataTable.isDataTable('#transferredStudentsTable')) {
                            $('#transferredStudentsTable').DataTable().destroy();
                        }

                        $('#activeStudentsTable').DataTable({
                            responsive: true,
                            pageLength: 10,
                            order: [[3, 'asc']]
                        });

                        $('#transferredStudentsTable').DataTable({
                            responsive: true,
                            pageLength: 10,
                            order: [[3, 'asc']]
                        });
                        
                    } else {
                        // No students found
                        window.allStudentsForExport = [];
                        renderStudentsTable([]);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading students:', error);
                    Swal.fire('Error', 'Failed to load students', 'error');
                }
            });
        }

        // Load Students
        function loadStudents() {
            if (isCoordinatorView && classID) {
                // Load students for all subclasses in the main class
                loadStudentsForCoordinator(classID);
                return;
            }
            
            if (!subclassID) return;

            $.ajax({
                url: '{{ url("get_subclass_students") }}/' + subclassID,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var activeTbody = $('#activeStudentsTable tbody');
                    var transferredTbody = $('#transferredStudentsTable tbody');
                    activeTbody.empty();
                    transferredTbody.empty();

                    if (response.students && response.students.length > 0) {
                        // Filter only active students first
                        var activeStudents = response.students.filter(function(s) {
                            var status = s.status || '';
                            return status === 'Active' || status === 'active' || status === '';
                        });
                        
                        // Calculate statistics from active students only
                        var activeTotal = activeStudents.length;
                        var activeMale = activeStudents.filter(function(s) { return s.gender === 'Male'; }).length;
                        var activeFemale = activeStudents.filter(function(s) { return s.gender === 'Female'; }).length;
                        var activeHealthIssues = activeStudents.filter(function(s) { return s.has_health_issues === true || s.has_health_issues === 'true'; }).length;
                        
                        // Update statistics display (only active students)
                        $('#statTotalStudents').text(activeTotal);
                        $('#statMaleStudents').text(activeMale);
                        $('#statFemaleStudents').text(activeFemale);
                        $('#statHealthIssues').text(activeHealthIssues);
                        
                        // Store all students data globally for statistics and filtering (only active)
                        allStudentsData = activeStudents.map(function(s) {
                            return {
                                studentID: s.studentID,
                                admission_number: s.admission_number,
                                gender: s.gender,
                                admission_date: s.admission_date,
                                subclassID: s.subclassID,
                                has_health_issues: s.has_health_issues || false,
                                status: s.status || 'Active'
                            };
                        });
                        
                        // Store full students data globally for export (only active)
                        window.allStudentsForExport = activeStudents.map(function(s) {
                            return {
                                studentID: s.studentID,
                                admission_number: s.admission_number || 'N/A',
                                first_name: s.first_name || '',
                                middle_name: s.middle_name || '',
                                last_name: s.last_name || '',
                                full_name: (s.first_name || '') + ' ' + (s.middle_name || '') + ' ' + (s.last_name || ''),
                                gender: s.gender || 'N/A',
                                date_of_birth: s.date_of_birth || 'N/A',
                                admission_date: s.admission_date || 'N/A',
                                subclassID: s.subclassID,
                                subclass_display: s.subclass_display || (s.class_name && s.subclass_name ? s.class_name + ' - ' + s.subclass_name : (s.subclass_name || 'N/A')),
                                parent_name: s.parent_name || 'Not Assigned',
                                address: s.address || 'N/A',
                                status: s.status || 'Active',
                                has_health_issues: s.has_health_issues || false,
                                photo: s.photo || null
                            };
                        });
                        console.log('Students data stored for export (loadStudents):', window.allStudentsForExport.length, 'students');
                        
                        var activeIndex = 0;
                        var transferredIndex = 0;
                        var schoolType = '{{ isset($school_details) ? $school_details->school_type : "Secondary" }}';

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

                        $.each(response.students, function(index, student) {
                            var baseUrl = '{{ asset("") }}';
                            var photoUrl = '';

                            // Get first letter of student name
                            var firstName = student.first_name || '';
                            var firstLetter = firstName ? firstName.charAt(0).toUpperCase() : 'N';

                            // Generate color based on student name
                            var studentFullName = (student.first_name || '') + ' ' + (student.last_name || '');
                            var placeholderColor = getColorFromName(studentFullName);

                            // Determine photo URL - path is public/userImages
                            if (student.photo) {
                                // Ensure we use correct path: public/userImages/filename
                                photoUrl = baseUrl + 'userImages/' + student.photo;
                            } else {
                                photoUrl = '';
                            }

                            // Create photo HTML with proper error handling and placeholder
                            // If image fails to load or doesn't exist, show first letter with random color
                            var photoHtml = '';
                            if (photoUrl) {
                                photoHtml = '<div style="position: relative; display: inline-block;">' +
                                    '<img src="' + photoUrl + '" alt="Student Photo" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #940000; cursor: pointer;" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">' +
                                    '<div class="rounded-circle d-none align-items-center justify-content-center text-white" style="width: 40px; height: 40px; background-color: ' + placeholderColor + '; font-size: 16px; font-weight: bold; border: 2px solid #940000;">' + firstLetter + '</div>' +
                                    '</div>';
                            } else {
                                photoHtml = '<div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px; background-color: ' + placeholderColor + '; font-size: 16px; font-weight: bold; border: 2px solid #940000;">' + firstLetter + '</div>';
                            }

                            var actionButtons = '<button class="btn btn-sm btn-info view-student-btn" data-student-id="' + student.studentID + '" title="View Details"><i class="bi bi-eye"></i></button> ' +
                                '<button class="btn btn-sm btn-warning edit-student-btn" data-student-id="' + student.studentID + '" title="Edit"><i class="bi bi-pencil-square"></i></button> ';

                            // Only show in Transferred tab if status is explicitly "Transferred"
                            if (student.status === 'Transferred') {
                                // Transferred Students
                                actionButtons += '<button class="btn btn-sm btn-success activate-student-btn" data-student-id="' + student.studentID + '" title="Activate Student"><i class="bi bi-check-circle"></i></button> ';
                                // Always show revert button if old_subclassID exists (student came from another class)
                                if (student.old_subclassID && student.old_subclass_info) {
                                    actionButtons += '<button class="btn btn-sm btn-secondary revert-transfer-btn" data-student-id="' + student.studentID + '" title="Revert to ' + (student.old_subclass_info.display_name || 'Previous Class') + '"><i class="bi bi-arrow-left-circle"></i></button> ';
                                }
                                actionButtons += '<button class="btn btn-sm btn-danger delete-student-btn" data-student-id="' + student.studentID + '" title="Delete"><i class="bi bi-trash-fill"></i></button>';

                                // Display grade/division
                                var gradeDisplay = '<span class="text-muted">N/A</span>';
                                var gradeClass = '';
                                if (student.student_grade) {
                                    var grade = student.student_grade;

                                    // Determine grade class based on grade format
                                    if (grade.startsWith('I.')) {
                                        gradeClass = 'bg-success text-white';
                                    } else if (grade.startsWith('II.')) {
                                        gradeClass = 'bg-info text-white';
                                    } else if (grade.startsWith('III.')) {
                                        gradeClass = 'bg-warning text-dark';
                                    } else if (grade.startsWith('IV.')) {
                                        gradeClass = 'bg-danger text-white';
                                    } else if (grade.startsWith('0.')) {
                                        gradeClass = 'bg-secondary text-white';
                                    } else if (grade === 'Division One') {
                                        gradeClass = 'bg-success text-white';
                                    } else if (grade === 'Division Two') {
                                        gradeClass = 'bg-info text-white';
                                    } else if (grade === 'Division Three') {
                                        gradeClass = 'bg-warning text-dark';
                                    } else if (grade === 'Division Four') {
                                        gradeClass = 'bg-danger text-white';
                            } else {
                                        gradeClass = 'bg-secondary text-white';
                                    }

                                    gradeDisplay = '<span class="badge ' + gradeClass + '">' + grade + '</span>';
                                }

                                // Previous class info - show subclass_name only
                                var previousClass = '<span class="text-muted">N/A</span>';
                                if (student.old_subclass_info) {
                                    previousClass = '<strong>' + (student.old_subclass_info.subclass_name || student.old_subclass_info.display_name || 'N/A') + '</strong>';
                                }

                                var subclassIDForFilter = student.subclassID || '';
                                var row = $('<tr>')
                                    .attr('data-subclass-id', subclassIDForFilter)
                                    .attr('data-admission-date', student.admission_date || '')
                                    .attr('data-has-health-issues', student.has_health_issues || false)
                                    .html(
                                        '<td>' + (transferredIndex + 1) + '</td>' +
                                        '<td>' + photoHtml + '</td>' +
                                        '<td>' + (student.admission_number || 'N/A') + '</td>' +
                                        '<td>' + (student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '') + '</td>' +
                                        '<td>' + (student.gender || 'N/A') + '</td>' +
                                        (isCoordinatorView ? '<td>' + subclassDisplay + '</td>' : '') +
                                        '<td>' + previousClass + '</td>' +
                                        '<td>' + gradeDisplay + '</td>' +
                                        '<td>' + (student.parent_name || 'Not Assigned') + '</td>' +
                                        '<td>' + actionButtons + '</td>'
                                    );
                                transferredTbody.append(row);
                                transferredIndex++;
                            } else {
                                // Active Students (all students that are not "Transferred" status)
                                // This includes: Active, Graduated, Inactive, etc.
                                actionButtons += '<button class="btn btn-sm btn-primary shift-student-btn" data-student-id="' + student.studentID + '" title="Shift Class"><i class="bi bi-arrow-right-circle"></i></button> ';

                                // Add Send to Fingerprint Device button (always show, backend will check if already has fingerprint_id)
                                actionButtons += '<a href="#" class="btn btn-sm btn-success text-white send-student-to-fingerprint-btn" data-student-id="' + student.studentID + '" data-student-name="' + (student.first_name || '') + '" data-fingerprint-id="' + (student.fingerprint_id || '') + '" title="Send to Fingerprint Device"><i class="bi bi-fingerprint"></i></a> ';

                                // Only show delete button for non-active students
                                if (student.status !== 'Active') {
                            actionButtons += '<button class="btn btn-sm btn-danger delete-student-btn" data-student-id="' + student.studentID + '" title="Delete"><i class="bi bi-trash-fill"></i></button>';
                                }

                                var statusBadge = '<span class="badge badge-success">' + (student.status || 'Active') + '</span>';

                            // Fingerprint Status
                            var fingerprintStatus = '';
                            if (student.fingerprint_id) {
                                if (student.sent_to_device) {
                                    var captureCount = student.fingerprint_capture_count || 0;
                                    if (captureCount >= 3) {
                                        fingerprintStatus = '<span class="badge badge-success"><i class="bi bi-check-circle"></i> Captured (ID: ' + student.fingerprint_id + ')</span>';
                                    } else if (captureCount > 0) {
                                        fingerprintStatus = '<span class="badge badge-warning"><i class="bi bi-hourglass-split"></i> In Progress (' + captureCount + '/3) - ID: ' + student.fingerprint_id + '</span>';
                                    } else {
                                        fingerprintStatus = '<span class="badge badge-success"><i class="bi bi-check-circle"></i> Sent (ID: ' + student.fingerprint_id + ')</span>';
                                    }
                                } else {
                                    fingerprintStatus = '<span class="badge badge-success"><i class="bi bi-check-circle"></i> Sent (ID: ' + student.fingerprint_id + ')</span>';
                                }
                            } else {
                                fingerprintStatus = '<span class="badge badge-secondary"><i class="bi bi-dash"></i> No ID</span>';
                            }

                                var subclassIDForFilter = student.subclassID || '';
                                var subclassDisplay = student.subclass_display || (student.class_name && student.subclass_name ? student.class_name + ' - ' + student.subclass_name : (student.subclass_name || 'N/A'));
                                
                                var row = $('<tr>')
                                    .attr('data-subclass-id', subclassIDForFilter)
                                    .attr('data-admission-date', student.admission_date || '')
                                    .attr('data-has-health-issues', student.has_health_issues || false)
                                    .html(
                                        '<td>' + (activeIndex + 1) + '</td>' +
                                        '<td>' + photoHtml + '</td>' +
                                        '<td>' + (student.admission_number || 'N/A') + '</td>' +
                                        '<td>' + (student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '') + '</td>' +
                                        '<td>' + (student.gender || 'N/A') + '</td>' +
                                        (isCoordinatorView ? '<td>' + subclassDisplay + '</td>' : '') +
                                        '<td>' + (student.parent_name || 'Not Assigned') + '</td>' +
                                        '<td>' + fingerprintStatus + '</td>' +
                                        '<td>' + statusBadge + '</td>' +
                                        '<td><div class="d-flex justify-content-center align-items-center gap-2">' + actionButtons + '</div></td>'
                                    );
                                activeTbody.append(row);
                                activeIndex++;
                            }
                        });

                        // Initialize DataTables
                        // Destroy existing DataTable instances if they exist
                        if ($.fn.DataTable.isDataTable('#activeStudentsTable')) {
                            $('#activeStudentsTable').DataTable().destroy();
                        }
                        if ($.fn.DataTable.isDataTable('#transferredStudentsTable')) {
                            $('#transferredStudentsTable').DataTable().destroy();
                        }

                        // Initialize DataTable for Active Students
                        $('#activeStudentsTable').DataTable({
                            pageLength: 25,
                            order: [[3, 'asc']], // Sort by Name column
                            language: {
                                search: "Search:",
                                lengthMenu: "Show _MENU_ students per page",
                                info: "Showing _START_ to _END_ of _TOTAL_ students",
                                infoEmpty: "No active students found",
                                infoFiltered: "(filtered from _MAX_ total students)",
                                emptyTable: "No active students found",
                                zeroRecords: "No matching students found",
                                paginate: {
                                    first: "First",
                                    last: "Last",
                                    next: "Next",
                                    previous: "Previous"
                                }
                            },
                            responsive: true,
                            columnDefs: [
                                { orderable: false, targets: [1, 8] } // Disable sorting on Photo and Actions columns
                            ]
                        });

                        // Initialize DataTable for Transferred Students
                        $('#transferredStudentsTable').DataTable({
                            pageLength: 25,
                            order: [[3, 'asc']], // Sort by Name column
                            language: {
                                search: "Search:",
                                lengthMenu: "Show _MENU_ students per page",
                                info: "Showing _START_ to _END_ of _TOTAL_ students",
                                infoEmpty: "No transferred students found",
                                infoFiltered: "(filtered from _MAX_ total students)",
                                emptyTable: "No transferred students found",
                                zeroRecords: "No matching students found",
                                paginate: {
                                    first: "First",
                                    last: "Last",
                                    next: "Next",
                                    previous: "Previous"
                                }
                            },
                            responsive: true,
                            columnDefs: [
                                { orderable: false, targets: [1, 8] } // Disable sorting on Photo and Actions columns
                            ]
                        });
                    } else {
                        // No students at all - initialize empty DataTables
                        if ($.fn.DataTable.isDataTable('#activeStudentsTable')) {
                            $('#activeStudentsTable').DataTable().destroy();
                        }
                        if ($.fn.DataTable.isDataTable('#transferredStudentsTable')) {
                            $('#transferredStudentsTable').DataTable().destroy();
                        }

                        // Initialize empty DataTable for Active Students
                        $('#activeStudentsTable').DataTable({
                            pageLength: 25,
                            language: {
                                search: "Search:",
                                lengthMenu: "Show _MENU_ students per page",
                                info: "Showing _START_ to _END_ of _TOTAL_ students",
                                infoEmpty: "No students found",
                                infoFiltered: "(filtered from _MAX_ total students)",
                                emptyTable: "No students found",
                                zeroRecords: "No matching students found",
                                paginate: {
                                    first: "First",
                                    last: "Last",
                                    next: "Next",
                                    previous: "Previous"
                                }
                            },
                            responsive: true,
                            columnDefs: [
                                { orderable: false, targets: [1, 8] }
                            ]
                        });

                        // Initialize empty DataTable for Transferred Students
                        $('#transferredStudentsTable').DataTable({
                            pageLength: 25,
                            language: {
                                search: "Search:",
                                lengthMenu: "Show _MENU_ students per page",
                                info: "Showing _START_ to _END_ of _TOTAL_ students",
                                infoEmpty: "No students found",
                                infoFiltered: "(filtered from _MAX_ total students)",
                                emptyTable: "No students found",
                                zeroRecords: "No matching students found",
                                paginate: {
                                    first: "First",
                                    last: "Last",
                                    next: "Next",
                                    previous: "Previous"
                                }
                            },
                            responsive: true,
                            columnDefs: [
                                { orderable: false, targets: [1, 8] }
                            ]
                        });
                    }
                },
                error: function(xhr) {
                    var errorMessage = 'Error loading students';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 403) {
                        errorMessage = 'You do not have permission to view students.';
                    } else if (xhr.status === 404) {
                        errorMessage = 'Subclass not found.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error occurred. Please try again.';
                    }
                    $('#activeStudentsTable tbody').html('<tr><td colspan="8" class="text-center text-danger"><i class="bi bi-exclamation-triangle"></i> ' + errorMessage + '</td></tr>');
                    $('#transferredStudentsTable tbody').html('<tr><td colspan="9" class="text-center text-danger"><i class="bi bi-exclamation-triangle"></i> ' + errorMessage + '</td></tr>');
                }
            });
        }

        // Load Parents
        function loadParents() {
            var requestData = {};
            
            if (isCoordinatorView && classID) {
                // Coordinator view - load parents for all subclasses in main class
                requestData = {
                    classID: classID,
                    coordinator: 'true'
                };
            } else if (subclassID) {
                // Class teacher view - load parents for specific subclass
                requestData = {
                    subclassID: subclassID,
                    filterBySubclass: true
                };
            } else {
                $('#parentsTable tbody').html('<tr><td colspan="7" class="text-center text-danger">Class ID not found</td></tr>');
                return;
            }

            console.log('Loading parents:', requestData);

            $.ajax({
                url: '{{ route("get_parents") }}',
                type: 'GET',
                data: requestData,
                dataType: 'json',
                success: function(response) {
                    console.log('Parents response:', response);
                    var tbody = $('#parentsTable tbody');
                    tbody.empty();

                    if (response.success && response.parents && response.parents.length > 0) {
                        $.each(response.parents, function(index, parent) {
                            var photoHtml = '<img src="' + (parent.photo || '{{ asset("images/default-avatar.png") }}') + '" alt="Parent Photo" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">';
                            if (!parent.photo) {
                                photoHtml = '<div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;"><i class="bi bi-person text-white" style="font-size: 1.5rem;"></i></div>';
                            }

                            var row = '<tr>' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td>' + photoHtml + '</td>' +
                                '<td>' + (parent.first_name || '') + ' ' + (parent.middle_name || '') + ' ' + (parent.last_name || '') + '</td>' +
                                '<td>' + (parent.phone || 'N/A') + '</td>' +
                                '<td>' + (parent.email || 'N/A') + '</td>' +
                                '<td>' + (parent.occupation || 'N/A') + '</td>' +
                                '<td>' +
                                    '<button class="btn btn-sm btn-info view-parent-btn" data-parent-id="' + parent.parentID + '" title="View Details"><i class="bi bi-eye"></i></button> ' +
                                    '<button class="btn btn-sm btn-warning edit-parent-btn" data-parent-id="' + parent.parentID + '" title="Edit"><i class="bi bi-pencil-square"></i></button> ' +
                                    '<button class="btn btn-sm btn-danger delete-parent-btn" data-parent-id="' + parent.parentID + '" title="Delete"><i class="bi bi-trash-fill"></i></button>' +
                                '</td>' +
                            '</tr>';
                            tbody.append(row);
                        });
                    } else {
                        tbody.append('<tr><td colspan="7" class="text-center">Hakuna wazazi walioorodheshwa kwenye darasa hili</td></tr>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading parents:', xhr, status, error);
                    $('#parentsTable tbody').html('<tr><td colspan="7" class="text-center text-danger">Error loading parents: ' + error + '</td></tr>');
                }
            });
        }

        // View Parent Details
        $(document).on('click', '.view-parent-btn', function() {
            var parentID = $(this).data('parent-id');
            $.ajax({
                url: '{{ url("get_parent") }}/' + parentID,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.parent) {
                        var parent = response.parent;
                        var photoHtml = '';
                        if (parent.photo) {
                            photoHtml = '<div class="col-md-12 text-center mb-3">' +
                                '<img src="' + parent.photo + '" alt="Parent Photo" class="img-fluid rounded" style="max-width: 200px; max-height: 200px;">' +
                                '</div>';
                        }
                        var studentsHtml = '';
                        if (parent.students && parent.students.length > 0) {
                            studentsHtml = '<div class="col-md-12 mt-3"><strong><i class="bi bi-people"></i> Students:</strong><ul class="list-group mt-2">';
                            $.each(parent.students, function(i, student) {
                                studentsHtml += '<li class="list-group-item">' + student.full_name + ' (' + student.admission_number + ') - ' + student.subclass_name + '</li>';
                            });
                            studentsHtml += '</ul></div>';
                        }
                        var html = photoHtml + '<div class="row">' +
                            '<div class="col-md-6"><strong><i class="bi bi-person"></i> Full Name:</strong></div><div class="col-md-6">' +
                                (parent.first_name || '') + ' ' + (parent.middle_name || '') + ' ' + (parent.last_name || '') + '</div>' +
                            '<div class="col-md-6"><strong><i class="bi bi-phone"></i> Phone:</strong></div><div class="col-md-6">' + (parent.phone || 'N/A') + '</div>' +
                            '<div class="col-md-6"><strong><i class="bi bi-envelope"></i> Email:</strong></div><div class="col-md-6">' + (parent.email || 'N/A') + '</div>' +
                            '<div class="col-md-6"><strong><i class="bi bi-briefcase"></i> Occupation:</strong></div><div class="col-md-6">' + (parent.occupation || 'N/A') + '</div>' +
                            '<div class="col-md-6"><strong><i class="bi bi-card-text"></i> National ID:</strong></div><div class="col-md-6">' + (parent.national_id || 'N/A') + '</div>' +
                            '<div class="col-md-6"><strong><i class="bi bi-gender-ambiguous"></i> Gender:</strong></div><div class="col-md-6">' + (parent.gender || 'N/A') + '</div>' +
                            '<div class="col-md-6"><strong><i class="bi bi-geo-alt"></i> Address:</strong></div><div class="col-md-6">' + (parent.address || 'N/A') + '</div>' +
                            studentsHtml +
                        '</div>';
                        $('#parentDetailsContent').html(html);
                        $('#viewParentDetailsModal').modal('show');
                    }
                }
            });
        });

        // Edit Parent
        $(document).on('click', '.edit-parent-btn', function() {
            var parentID = $(this).data('parent-id');
            $.ajax({
                url: '{{ url("get_parent") }}/' + parentID,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.parent) {
                        var parent = response.parent;
                        var photoPreview = '';
                        if (parent.photo) {
                            photoPreview = '<div class="mb-3"><img src="' + parent.photo + '" alt="Current Photo" class="img-fluid rounded" style="max-width: 150px; max-height: 150px;"></div>';
                        }
                        var html = '<input type="hidden" name="parentID" value="' + parent.parentID + '">' +
                            photoPreview +
                            '<div class="row">' +
                                '<div class="col-md-4">' +
                                    '<div class="form-group">' +
                                        '<label>First Name <span class="text-danger">*</span></label>' +
                                        '<input type="text" class="form-control" name="first_name" value="' + (parent.first_name || '') + '" required>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="col-md-4">' +
                                    '<div class="form-group">' +
                                        '<label>Middle Name</label>' +
                                        '<input type="text" class="form-control" name="middle_name" value="' + (parent.middle_name || '') + '">' +
                                    '</div>' +
                                '</div>' +
                                '<div class="col-md-4">' +
                                    '<div class="form-group">' +
                                        '<label>Last Name <span class="text-danger">*</span></label>' +
                                        '<input type="text" class="form-control" name="last_name" value="' + (parent.last_name || '') + '" required>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="row">' +
                                '<div class="col-md-6">' +
                                    '<div class="form-group">' +
                                        '<label>Gender</label>' +
                                        '<select class="form-control" name="gender">' +
                                            '<option value="">Select Gender</option>' +
                                            '<option value="Male" ' + (parent.gender === 'Male' ? 'selected' : '') + '>Male</option>' +
                                            '<option value="Female" ' + (parent.gender === 'Female' ? 'selected' : '') + '>Female</option>' +
                                        '</select>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="col-md-6">' +
                                    '<div class="form-group">' +
                                        '<label>Phone Number <span class="text-danger">*</span></label>' +
                                        '<input type="text" class="form-control" name="phone" value="' + (parent.phone || '') + '" required>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="row">' +
                                '<div class="col-md-6">' +
                                    '<div class="form-group">' +
                                        '<label>Email</label>' +
                                        '<input type="email" class="form-control" name="email" value="' + (parent.email || '') + '">' +
                                    '</div>' +
                                '</div>' +
                                '<div class="col-md-6">' +
                                    '<div class="form-group">' +
                                        '<label>Occupation</label>' +
                                        '<input type="text" class="form-control" name="occupation" value="' + (parent.occupation || '') + '">' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="row">' +
                                '<div class="col-md-6">' +
                                    '<div class="form-group">' +
                                        '<label>National ID</label>' +
                                        '<input type="text" class="form-control" name="national_id" value="' + (parent.national_id || '') + '">' +
                                    '</div>' +
                                '</div>' +
                                '<div class="col-md-6">' +
                                    '<div class="form-group">' +
                                        '<label>Address</label>' +
                                        '<textarea class="form-control" name="address" rows="2">' + (parent.address || '') + '</textarea>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="form-group">' +
                                '<label>Update Photo</label>' +
                                '<input type="file" class="form-control-file" name="photo" accept="image/*">' +
                                '<small class="form-text text-muted">Max size: 2MB. Formats: JPG, JPEG, PNG</small>' +
                            '</div>';
                        $('#editParentContent').html(html);
                        $('#editParentModal').modal('show');
                    }
                }
            });
        });

        // Delete Parent
        $(document).on('click', '.delete-parent-btn', function() {
            var parentID = $(this).data('parent-id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will permanently delete this parent',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ url("delete_parent") }}/' + parentID,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', response.message || 'Parent deleted successfully', 'success')
                                    .then(function() {
                                        loadParents();
                                    });
                            } else {
                                Swal.fire('Error', response.message || 'Failed to delete parent', 'error');
                            }
                        },
                        error: function(xhr) {
                            var errorMsg = 'Network error occurred';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire('Error', errorMsg, 'error');
                        }
                    });
                }
            });
        });

        // Edit Parent Form
        $('#editParentForm').on('submit', function(e) {
            e.preventDefault();

            $('.text-danger').hide().text('');
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            var formData = new FormData(this);
            var submitBtn = $(this).find('button[type="submit"]');
            var originalText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

            $.ajax({
                url: '{{ route("update_parent") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(response) {
                    submitBtn.prop('disabled', false).html(originalText);
                    if (response.success) {
                        Swal.fire('Success', response.message || 'Parent updated successfully', 'success')
                            .then(function() {
                                $('#editParentModal').modal('hide');
                                loadParents();
                            });
                    } else {
                        Swal.fire('Error', response.message || 'Failed to update parent', 'error');
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, message) {
                            var input = $('[name="' + field + '"]');
                            if (input.length > 0) {
                                input.addClass('is-invalid');
                                input.after('<div class="invalid-feedback">' + message + '</div>');
                            }
                        });
                        Swal.fire('Validation Error', 'Please check the form for errors', 'error');
                    } else {
                        var errorMsg = 'Network error occurred';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', errorMsg, 'error');
                    }
                }
            });
        });

        // Load Parents Dropdown
        function loadParentsDropdown() {
            $.ajax({
                url: '{{ route("get_parents") }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var select = $('#parentID');
                    select.empty().append('<option value="">Select Parent (Optional)</option>');
                    if (response.parents && response.parents.length > 0) {
                        $.each(response.parents, function(index, parent) {
                            select.append('<option value="' + parent.parentID + '">' +
                                (parent.first_name || '') + ' ' + (parent.last_name || '') +
                                ' (' + (parent.phone || '') + ')' +
                            '</option>');
                        });
                    }
                }
            });
        }

        // View Student Details - Using same handler as manage_student.blade.php
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

        // Edit Student
        $(document).on('click', '.edit-student-btn', function() {
            var studentID = $(this).data('student-id');
            $.ajax({
                url: '{{ url("get_student") }}/' + studentID,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.student) {
                        var student = response.student;
                        var photoPreview = '';
                        if (student.photo) {
                            photoPreview = '<div class="mb-3"><img src="' + student.photo + '" alt="Current Photo" class="img-fluid rounded" style="max-width: 150px; max-height: 150px;"></div>';
                        }
                        var html = '<input type="hidden" name="studentID" value="' + student.studentID + '">' +
                            photoPreview +
                            '<div class="row">' +
                                '<div class="col-md-6">' +
                                    '<div class="form-group">' +
                                        '<label>First Name <span class="text-danger">*</span></label>' +
                                        '<input type="text" class="form-control" name="first_name" value="' + (student.first_name || '') + '" required>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="col-md-6">' +
                                    '<div class="form-group">' +
                                        '<label>Middle Name</label>' +
                                        '<input type="text" class="form-control" name="middle_name" value="' + (student.middle_name || '') + '">' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="row">' +
                                '<div class="col-md-6">' +
                                    '<div class="form-group">' +
                                        '<label>Last Name <span class="text-danger">*</span></label>' +
                                        '<input type="text" class="form-control" name="last_name" value="' + (student.last_name || '') + '" required>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="col-md-6">' +
                                    '<div class="form-group">' +
                                        '<label>Gender <span class="text-danger">*</span></label>' +
                                        '<select class="form-control" name="gender" required>' +
                                            '<option value="">Select Gender</option>' +
                                            '<option value="Male" ' + (student.gender === 'Male' ? 'selected' : '') + '>Male</option>' +
                                            '<option value="Female" ' + (student.gender === 'Female' ? 'selected' : '') + '>Female</option>' +
                                        '</select>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="row">' +
                                '<div class="col-md-6">' +
                                    '<div class="form-group">' +
                                        '<label>Admission Number <span class="text-danger">*</span></label>' +
                                        '<input type="text" class="form-control" name="admission_number" value="' + (student.admission_number || '') + '" required>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="col-md-6">' +
                                    '<div class="form-group">' +
                                        '<label>Date of Birth</label>' +
                                        '<input type="date" class="form-control" name="date_of_birth" value="' + (student.date_of_birth || '') + '">' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="row">' +
                                '<div class="col-md-6">' +
                                    '<div class="form-group">' +
                                        '<label>Admission Date</label>' +
                                        '<input type="date" class="form-control" name="admission_date" value="' + (student.admission_date || '') + '">' +
                                    '</div>' +
                                '</div>' +
                                '<div class="col-md-6">' +
                                    '<div class="form-group">' +
                                        '<label>Status</label>' +
                                        '<select class="form-control" name="status">' +
                                            '<option value="Active" ' + (student.status === 'Active' ? 'selected' : '') + '>Active</option>' +
                                            '<option value="Transferred" ' + (student.status === 'Transferred' ? 'selected' : '') + '>Transferred</option>' +
                                            '<option value="Graduated" ' + (student.status === 'Graduated' ? 'selected' : '') + '>Graduated</option>' +
                                            '<option value="Inactive" ' + (student.status === 'Inactive' ? 'selected' : '') + '>Inactive</option>' +
                                        '</select>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="form-group">' +
                                '<label>Parent/Guardian</label>' +
                                '<select class="form-control" name="parentID" id="edit_parentID">' +
                                    '<option value="">Select Parent (Optional)</option>' +
                                '</select>' +
                            '</div>' +
                            '<div class="form-group">' +
                                '<label>Address</label>' +
                                '<textarea class="form-control" name="address" rows="2">' + (student.address || '') + '</textarea>' +
                            '</div>' +
                            '<div class="form-group">' +
                                '<label>Update Photo</label>' +
                                '<input type="file" class="form-control-file" name="photo" accept="image/*">' +
                                '<small class="form-text text-muted">Max size: 2MB. Formats: JPG, JPEG, PNG</small>' +
                            '</div>';
                        $('#editStudentContent').html(html);

                        // Load parents dropdown
                        loadParentsDropdownForEdit(student.parentID);

                        $('#editStudentModal').modal('show');
                    }
                }
            });
        });

        // Load Parents Dropdown for Edit
        function loadParentsDropdownForEdit(selectedParentID) {
            $.ajax({
                url: '{{ route("get_parents") }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var select = $('#edit_parentID');
                    select.empty().append('<option value="">Select Parent (Optional)</option>');
                    if (response.parents && response.parents.length > 0) {
                        $.each(response.parents, function(index, parent) {
                            var selected = (selectedParentID && parent.parentID == selectedParentID) ? 'selected' : '';
                            select.append('<option value="' + parent.parentID + '" ' + selected + '>' +
                                (parent.first_name || '') + ' ' + (parent.last_name || '') +
                                ' (' + (parent.phone || '') + ')' +
                            '</option>');
                        });
                    }
                }
            });
        }

        // Shift Student (Manual Transfer - Show All Subclasses)
        $(document).on('click', '.shift-student-btn', function() {
            var studentID = $(this).data('student-id');
            $('#shift_studentID').val(studentID);

            // Load all subclasses for manual transfer (no grade checking)
            $.ajax({
                url: '/get_subclasses_for_school',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                        var select = $('#new_subclassID');
                    select.empty();

                    if (response.success && response.subclasses && response.subclasses.length > 0) {
                        select.prop('disabled', false);
                        select.append('<option value="">Select Class</option>');

                        $.each(response.subclasses, function(index, subclass) {
                            select.append('<option value="' + subclass.subclassID + '">' + subclass.display_name + '</option>');
                        });
                        $('#shiftStudentModal').modal('show');
                    } else {
                        select.append('<option value="">No classes found</option>');
                        select.prop('disabled', true);
                        Swal.fire({
                            title: 'No Classes Found',
                            text: 'No classes available for transfer.',
                            icon: 'info',
                            confirmButtonColor: '#940000'
                        });
                        $('#shiftStudentModal').modal('show');
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'Failed to load classes';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        });

        // Delete Student
        // Handle Send Student to Fingerprint Device Button Click
        $(document).off('click', '.send-student-to-fingerprint-btn').on('click', '.send-student-to-fingerprint-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Fingerprint button clicked');
            
            var studentId = $(this).data('student-id');
            var studentName = $(this).data('student-name');
            var fingerprintId = $(this).data('fingerprint-id') || '';
            var $btn = $(this);
            var originalHtml = $btn.html();

            console.log('Student ID:', studentId, 'Name:', studentName, 'Fingerprint ID:', fingerprintId);

            // Check if student already has a fingerprint ID
            if (fingerprintId && fingerprintId.toString().trim() !== '') {
                Swal.fire({
                    icon: 'info',
                    title: 'Fingerprint ID Already Assigned',
                    html: 'This student already has a <strong>FINGERPRINT ID</strong>.<br><br>' +
                          'Please if it is not found on the device, register student to the device with this ID: <strong>' + fingerprintId + '</strong>',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6'
                });
                return false;
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
            
            return false;
        });

        $(document).on('click', '.delete-student-btn', function() {
            var studentID = $(this).data('student-id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will permanently delete this student',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ url("delete_student") }}/' + studentID,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', response.message || 'Student deleted successfully', 'success')
                                    .then(function() {
                                        loadStudents();
                                    });
                            } else {
                                Swal.fire('Error', response.message || 'Failed to delete student', 'error');
                            }
                        },
                        error: function(xhr) {
                            var errorMsg = 'Network error occurred';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire('Error', errorMsg, 'error');
                        }
                    });
                }
            });
        });

        // Shift Student Form
        $('#shiftStudentForm').on('submit', function(e) {
            e.preventDefault();

            var formData = $(this).serialize();
            var submitBtn = $(this).find('button[type="submit"]');
            var originalText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Transferring...');

            $.ajax({
                url: '{{ route("transfer_student") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(response) {
                    submitBtn.prop('disabled', false).html(originalText);
                    if (response.success) {
                        Swal.fire('Success', response.message || 'Student transferred successfully', 'success')
                            .then(function() {
                                $('#shiftStudentModal').modal('hide');
                                $('#shiftStudentForm')[0].reset();
                                loadStudents();
                            });
                    } else {
                        Swal.fire('Error', response.message || 'Failed to transfer student', 'error');
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html(originalText);
                    var errorMsg = 'Network error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMsg = errors.join('<br>');
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        });

        // Edit Student Form
        $('#editStudentForm').on('submit', function(e) {
            e.preventDefault();

            $('.text-danger').hide().text('');
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            var formData = new FormData(this);
            var submitBtn = $(this).find('button[type="submit"]');
            var originalText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

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
                success: function(response) {
                    submitBtn.prop('disabled', false).html(originalText);
                    if (response.success) {
                        Swal.fire('Success', response.message || 'Student updated successfully', 'success')
                            .then(function() {
                                $('#editStudentModal').modal('hide');
                                loadStudents();
                            });
                    } else {
                        Swal.fire('Error', response.message || 'Failed to update student', 'error');
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, message) {
                            var input = $('[name="' + field + '"]');
                            if (input.length > 0) {
                                input.addClass('is-invalid');
                                input.after('<div class="invalid-feedback">' + message + '</div>');
                            }
                        });
                        Swal.fire('Validation Error', 'Please check the form for errors', 'error');
                    } else {
                        var errorMsg = 'Network error occurred';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', errorMsg, 'error');
                    }
                }
            });
        });

        // Activate Student (for transferred students)
        $(document).on('click', '.activate-student-btn', function() {
            var studentID = $(this).data('student-id');
            Swal.fire({
                title: 'Activate Student?',
                text: 'This will activate the student in the current class',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, activate!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ url("activate_student") }}/' + studentID,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Success', response.message || 'Student activated successfully', 'success')
                                    .then(function() {
                                        loadStudents();
                                    });
                            } else {
                                Swal.fire('Error', response.message || 'Failed to activate student', 'error');
                            }
                        },
                        error: function(xhr) {
                            var errorMsg = 'Network error occurred';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire('Error', errorMsg, 'error');
                        }
                    });
                }
            });
        });

        // Activate Student from Modal (for transferred students)
        $(document).on('click', '.activate-student-modal-btn', function() {
            var studentID = $(this).data('student-id');
            Swal.fire({
                title: 'Activate Student?',
                text: 'This will activate the student in the current class',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, activate!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ url("activate_student") }}/' + studentID,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Success', response.message || 'Student activated successfully', 'success')
                                    .then(function() {
                                        $('#viewStudentDetailsModal').modal('hide');
                                        loadStudents();
                                    });
                            } else {
                                Swal.fire('Error', response.message || 'Failed to activate student', 'error');
                            }
                        },
                        error: function(xhr) {
                            var errorMsg = 'Network error occurred';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire('Error', errorMsg, 'error');
                        }
                    });
                }
            });
        });

        // Revert Transfer from Modal (for transferred students)
        $(document).on('click', '.revert-transfer-modal-btn', function() {
            var studentID = $(this).data('student-id');
            Swal.fire({
                title: 'Revert Transfer?',
                text: 'This will return the student to their previous class',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, revert!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ url("revert_transfer") }}/' + studentID,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Success', response.message || 'Student reverted to previous class successfully', 'success')
                                    .then(function() {
                                        $('#viewStudentDetailsModal').modal('hide');
                                        loadStudents();
                                    });
                            } else {
                                Swal.fire('Error', response.message || 'Failed to revert transfer', 'error');
                            }
                        },
                        error: function(xhr) {
                            var errorMsg = 'Network error occurred';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire('Error', errorMsg, 'error');
                        }
                    });
                }
            });
        });

        // Revert Transfer (for transferred students)
        $(document).on('click', '.revert-transfer-btn', function() {
            var studentID = $(this).data('student-id');
            Swal.fire({
                title: 'Revert Transfer?',
                text: 'This will return the student to their previous class',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, revert!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ url("revert_transfer") }}/' + studentID,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Success', response.message || 'Student reverted to previous class successfully', 'success')
                                    .then(function() {
                                        loadStudents();
                                    });
                            } else {
                                Swal.fire('Error', response.message || 'Failed to revert transfer', 'error');
                            }
                        },
                        error: function(xhr) {
                            var errorMsg = 'Network error occurred';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire('Error', errorMsg, 'error');
                        }
                    });
                }
            });
        });


        // Download Parents PDF
        $('#downloadParentsPdfBtn').on('click', function() {
            if (!subclassID) {
                Swal.fire('Error', 'Class ID not found', 'error');
                return;
            }

            var btn = $(this);
            var originalText = btn.html();
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generating PDF...');

            $.ajax({
                url: '{{ url("get_parents_for_pdf") }}/' + subclassID,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    btn.prop('disabled', false).html(originalText);

                    if (response.success) {
                        generateParentsPDF(response);
                    } else {
                        Swal.fire('Error', response.message || 'Failed to fetch data', 'error');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(originalText);
                    Swal.fire('Error', 'Failed to fetch data for PDF', 'error');
                }
            });
        });

        // Store all examinations for search
        var allExaminations = [];
        var selectedExamID = null;

        // Load Examinations for Class
        function loadExaminationsForClass() {
            if (!subclassID) return;

            $('#examinationsList').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"></div></div>');

            $.ajax({
                url: '/get_examinations_for_subclass/' + subclassID,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.examinations) {
                        allExaminations = response.examinations;
                        displayExaminations(allExaminations);
                        initializeExamSearch();
                    } else {
                        $('#examinationsList').html('<div class="alert alert-info">No examinations found for this school.</div>');
                    }
                },
                error: function(xhr) {
                    $('#examinationsList').html('<div class="alert alert-danger">Failed to load examinations.</div>');
                    Swal.fire('Error', 'Failed to load examinations', 'error');
                }
            });
        }

        // Display examinations list
        function displayExaminations(exams) {
            if (!exams || exams.length === 0) {
                $('#examinationsList').html('<div class="alert alert-info">No examinations found.</div>');
                return;
            }

            var html = '<div class="list-group">';
            exams.forEach(function(exam) {
                var examTypeText = exam.exam_type ? exam.exam_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A';
                var statusClass = exam.status === 'Active' ? 'badge-success' : 'badge-secondary';
                var isSelected = selectedExamID == exam.examID ? 'active' : '';

                html += '<a href="#" class="list-group-item list-group-item-action exam-item ' + isSelected + '" data-exam-id="' + exam.examID + '" style="cursor: pointer; border-left: 4px solid ' + (isSelected ? '#940000' : '#dee2e6') + ';">';
                html += '<div class="d-flex w-100 justify-content-between">';
                html += '<h6 class="mb-1">' + exam.exam_name + '</h6>';
                html += '<span class="badge ' + statusClass + '">' + exam.status + '</span>';
                html += '</div>';
                html += '<p class="mb-1"><small class="text-muted">Year: ' + exam.year + ' | Type: ' + examTypeText + '</small></p>';
                if (exam.start_date && exam.end_date) {
                    html += '<small class="text-muted">' + exam.start_date + ' to ' + exam.end_date + '</small>';
                }
                html += '</a>';
            });
            html += '</div>';

            $('#examinationsList').html(html);

            // Add click handlers
            $('.exam-item').on('click', function(e) {
                e.preventDefault();
                var examID = $(this).data('exam-id');
                selectExamination(examID);
            });
        }

        // Select examination
        function selectExamination(examID) {
            selectedExamID = examID;

            // Update UI
            $('.exam-item').removeClass('active').css('border-left-color', '#dee2e6');
            $('.exam-item[data-exam-id="' + examID + '"]').addClass('active').css('border-left-color', '#940000');

            // Load results for selected examination
            loadClassResults(examID);
        }

        // Initialize exam search
        function initializeExamSearch() {
            $('#examSearchInput').off('keyup').on('keyup', function() {
                var searchTerm = $(this).val().toLowerCase();
                var filtered = allExaminations.filter(function(exam) {
                    return exam.exam_name.toLowerCase().includes(searchTerm) ||
                           exam.year.toString().includes(searchTerm) ||
                           exam.status.toLowerCase().includes(searchTerm) ||
                           (exam.exam_type && exam.exam_type.toLowerCase().includes(searchTerm));
                });
                displayExaminations(filtered);
            });
        }

        // Load Class Results
        function loadClassResults(examID) {
            if (!subclassID) return;

            $('#resultsContent').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"></div></div>');

            var url = examID ? '/get_subclass_results/' + subclassID + '/' + examID : '/get_subclass_results/' + subclassID;

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.results) {
                        var schoolType = response.school_type || 'Secondary';
                        var baseUrl = '{{ asset("") }}';

                        var html = '<div class="table-responsive">';
                        html += '<table class="table table-hover table-striped table-bordered">';
                        html += '<thead class="bg-primary-custom text-white">';
                        html += '<tr>';
                        html += '<th>#</th>';
                        html += '<th>Photo</th>';
                        html += '<th>Admission No.</th>';
                        html += '<th>Student Name</th>';
                        html += '<th>Total Marks</th>';
                        html += '<th>Average</th>';
                        html += '<th>' + (schoolType === 'Primary' ? 'Division' : 'Grade') + '</th>';
                        html += '<th>Position</th>';
                        html += '<th>Actions</th>';
                        html += '</tr>';
                        html += '</thead>';
                        html += '<tbody>';

                        response.results.forEach(function(result, index) {
                            var photoUrl = '';
                            if (result.photo) {
                                photoUrl = baseUrl + 'userImages/' + result.photo;
                            } else {
                                photoUrl = result.gender === 'Female'
                                    ? baseUrl + 'images/female.png'
                                    : baseUrl + 'images/male.png';
                            }
                            var fallbackPhoto = result.gender === 'Female' ? baseUrl + 'images/female.png' : baseUrl + 'images/male.png';

                            var gradeOrDivision = '';
                            var gradeClass = '';
                            var className = response.class_name ? response.class_name.toLowerCase() : '';
                            var isSecondaryWithDivision = schoolType === 'Secondary' && ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six'].includes(className);

                            if (schoolType === 'Primary' || isSecondaryWithDivision) {
                                // Display division for Primary or Secondary O-Level/A-Level
                                gradeOrDivision = result.total_division || (schoolType === 'Primary' ? 'Division Zero' : '0.0');

                                // Handle division format (I.7, II.20, etc. or Division One, etc.)
                                if (gradeOrDivision.startsWith('I.')) {
                                    gradeClass = 'bg-success text-white';
                                } else if (gradeOrDivision.startsWith('II.')) {
                                    gradeClass = 'bg-info text-white';
                                } else if (gradeOrDivision.startsWith('III.')) {
                                    gradeClass = 'bg-warning text-dark';
                                } else if (gradeOrDivision.startsWith('IV.')) {
                                    gradeClass = 'bg-danger text-white';
                                } else if (gradeOrDivision.startsWith('0.')) {
                                    gradeClass = 'bg-secondary text-white';
                                } else if (gradeOrDivision === 'Division One') {
                                    gradeClass = 'bg-success text-white';
                                } else if (gradeOrDivision === 'Division Two') {
                                    gradeClass = 'bg-info text-white';
                                } else if (gradeOrDivision === 'Division Three') {
                                    gradeClass = 'bg-warning text-dark';
                                } else if (gradeOrDivision === 'Division Four') {
                                    gradeClass = 'bg-danger text-white';
                                } else {
                                    gradeClass = 'bg-secondary text-white';
                                }
                            } else {
                                // Display grade for other secondary schools
                                gradeOrDivision = result.total_grade || 'Incomplete';
                                if (gradeOrDivision === 'A') {
                                    gradeClass = 'bg-success text-white';
                                } else if (gradeOrDivision === 'F' || gradeOrDivision === 'E' || gradeOrDivision === 'S/F') {
                                    gradeClass = 'bg-danger text-white';
                                } else if (gradeOrDivision === 'Incomplete') {
                                    gradeClass = 'bg-warning text-dark';
                                } else {
                                    gradeClass = 'bg-info text-white';
                                }
                            }

                            html += '<tr>';
                            html += '<td>' + (index + 1) + '</td>';
                            html += '<td>';
                            html += '<img src="' + photoUrl + '" alt="Student Photo" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #940000; cursor: pointer;" onerror="this.src=\'' + fallbackPhoto + '\'">';
                            html += '</td>';
                            html += '<td><strong>' + (result.admission_number || 'N/A') + '</strong></td>';
                            html += '<td>' + (result.first_name || '') + ' ' + (result.middle_name || '') + ' ' + (result.last_name || '') + '</td>';
                            html += '<td><strong>' + (result.total_marks || 0) + '</strong></td>';
                            html += '<td><strong>' + (result.average_marks ? result.average_marks.toFixed(2) : '0.00') + '</strong></td>';
                            html += '<td><span class="badge ' + gradeClass + '" style="font-size: 0.9rem; padding: 0.4rem 0.6rem;">' + gradeOrDivision + '</span></td>';
                            var classPosText = (result.class_position || 'N/A');
                            if (result.total_class_students && result.class_position) {
                                classPosText = result.class_position + ' out of ' + result.total_class_students;
                            }
                            html += '<td><strong class="text-info">' + classPosText + '</strong></td>';
                            html += '<td>';
                            html += '<button class="btn btn-sm btn-info view-more-results-btn" data-student-id="' + result.studentID + '" data-exam-id="' + (examID || '') + '" title="View More Results">';
                            html += '<i class="bi bi-eye"></i> View More';
                            html += '</button>';
                            html += '</td>';
                            html += '</tr>';
                        });

                        html += '</tbody>';
                        html += '</table>';
                        html += '</div>';

                        $('#resultsContent').html(html);
                    } else {
                        $('#resultsContent').html('<div class="alert alert-info">No results found for this class.</div>');
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'Error loading results.';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    } else if (xhr.status === 404) {
                        errorMsg = 'Results not found for this class.';
                    } else if (xhr.status === 403) {
                        errorMsg = 'Unauthorized access.';
                    }
                    $('#resultsContent').html('<div class="alert alert-danger">' + errorMsg + '</div>');
                    console.error('Error loading results:', xhr);
                }
            });
        }

        // View More Results
        $(document).on('click', '.view-more-results-btn', function() {
            var studentID = $(this).data('student-id');
            var examID = $(this).data('exam-id') || '';

            $('#viewMoreResultsModal').modal('show');
            $('#moreResultsModalBody').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"></div></div>');

            var url = examID ? '/get_student_detailed_results/' + studentID + '/' + examID : '/get_student_detailed_results/' + studentID;

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var schoolType = response.school_type || 'Secondary';
                        var student = response.student;

                        // Store student data and exam results for PDF generation
                        currentStudentData = student;
                        currentExamResults = response.results || [];

                        var baseUrl = '{{ asset("") }}';

                        var photoUrl = '';
                        if (student.photo) {
                            photoUrl = baseUrl + 'userImages/' + student.photo;
                        } else {
                            photoUrl = student.gender === 'Female'
                                ? baseUrl + 'images/female.png'
                                : baseUrl + 'images/male.png';
                        }
                        var fallbackPhoto = student.gender === 'Female' ? baseUrl + 'images/female.png' : baseUrl + 'images/male.png';

                        var html = '<div class="mb-4">';
                        html += '<div class="row mb-3">';
                        html += '<div class="col-md-2 text-center">';
                        html += '<img src="' + photoUrl + '" alt="Student Photo" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #940000;" onerror="this.src=\'' + fallbackPhoto + '\'">';
                        html += '</div>';
                        html += '<div class="col-md-10">';
                        html += '<h5 class="text-primary-custom">' + (student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '') + '</h5>';
                        html += '<p class="mb-1"><strong>Admission No.:</strong> ' + (student.admission_number || 'N/A') + '</p>';
                        html += '<p class="mb-1"><strong>Class:</strong> ' + (student.subclass ? (student.subclass.subclass_name || 'N/A') : 'N/A') + '</p>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';

                        if (response.results && response.results.length > 0) {
                            response.results.forEach(function(examResult) {
                                var gradeOrDivision = '';
                                var gradeClass = '';
                                var className = response.class_name ? response.class_name.toLowerCase() : '';
                                var isSecondaryWithDivision = schoolType === 'Secondary' && ['form_one', 'form_two', 'form_three', 'form_four', 'form_five', 'form_six'].includes(className);

                                if (schoolType === 'Primary' || isSecondaryWithDivision) {
                                    // Display division for Primary or Secondary O-Level/A-Level
                                    gradeOrDivision = examResult.total_division || (schoolType === 'Primary' ? 'Division Zero' : '0.0');

                                    // Handle division format (I.7, II.20, etc. or Division One, etc.)
                                    if (gradeOrDivision.startsWith('I.')) {
                                        gradeClass = 'bg-success text-white';
                                    } else if (gradeOrDivision.startsWith('II.')) {
                                        gradeClass = 'bg-info text-white';
                                    } else if (gradeOrDivision.startsWith('III.')) {
                                        gradeClass = 'bg-warning text-dark';
                                    } else if (gradeOrDivision.startsWith('IV.')) {
                                        gradeClass = 'bg-danger text-white';
                                    } else if (gradeOrDivision.startsWith('0.')) {
                                        gradeClass = 'bg-secondary text-white';
                                    } else if (gradeOrDivision === 'Division One') {
                                        gradeClass = 'bg-success text-white';
                                    } else if (gradeOrDivision === 'Division Two') {
                                        gradeClass = 'bg-info text-white';
                                    } else if (gradeOrDivision === 'Division Three') {
                                        gradeClass = 'bg-warning text-dark';
                                    } else if (gradeOrDivision === 'Division Four') {
                                        gradeClass = 'bg-danger text-white';
                                    } else {
                                        gradeClass = 'bg-secondary text-white';
                                    }
                                } else {
                                    // Display grade for other secondary schools
                                    gradeOrDivision = examResult.total_grade || 'Incomplete';
                                    if (gradeOrDivision === 'A') {
                                        gradeClass = 'bg-success text-white';
                                    } else if (gradeOrDivision === 'F' || gradeOrDivision === 'E' || gradeOrDivision === 'S/F') {
                                        gradeClass = 'bg-danger text-white';
                                    } else if (gradeOrDivision === 'Incomplete') {
                                        gradeClass = 'bg-warning text-dark';
                                    } else {
                                        gradeClass = 'bg-info text-white';
                                    }
                                }

                                html += '<div class="card mb-4">';
                                html += '<div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center">';
                                html += '<h6 class="mb-0">' + examResult.exam_name + ' (' + examResult.year + ')</h6>';
                                html += '<button class="btn btn-sm btn-light download-pdf-btn" data-student-id="' + studentID + '" data-exam-id="' + examResult.examID + '" title="Download PDF">';
                                html += '<i class="bi bi-file-pdf"></i> Download PDF';
                                html += '</button>';
                                html += '</div>';
                                html += '<div class="card-body">';
                                html += '<div class="row mb-3">';
                                html += '<div class="col-md-3"><strong>Total Marks:</strong> ' + examResult.total_marks + '</div>';
                                html += '<div class="col-md-3"><strong>Average:</strong> ' + examResult.average_marks.toFixed(2) + '</div>';
                                html += '<div class="col-md-3"><strong>Subjects:</strong> ' + examResult.subject_count + '</div>';
                        // Removed subclass position - using class position only
                        html += '</div>';
                        html += '<div class="row mb-3">';
                        var classPosText = (examResult.class_position || 'N/A');
                        if (examResult.total_class_students && examResult.class_position) {
                            classPosText = examResult.class_position + ' out of ' + examResult.total_class_students;
                        }
                        html += '<div class="col-md-3"><strong>Position:</strong> <span class="text-info">' + classPosText + '</span></div>';
                                // Check if secondary school to display Division instead of Grade
                                var displayLabel = 'Grade';
                                if (schoolType === 'Primary') {
                                    displayLabel = 'Division';
                                } else if (isSecondaryWithDivision) {
                                    displayLabel = 'Division';
                                }
                                html += '<div class="col-md-3"><strong>Total ' + displayLabel + ':</strong> ';
                                html += '<span class="badge ' + gradeClass + '">' + gradeOrDivision + '</span></div>';
                                html += '</div>';

                                html += '<div class="table-responsive">';
                                html += '<table class="table table-hover table-striped">';
                                html += '<thead class="bg-light">';
                                html += '<tr>';
                                html += '<th>#</th>';
                                html += '<th>Subject</th>';
                                html += '<th>Code</th>';
                                html += '<th>Marks</th>';
                                html += '<th>' + (schoolType === 'Primary' ? 'Division' : 'Grade') + '</th>';
                                html += '<th>Remark</th>';
                                html += '</tr>';
                                html += '</thead>';
                                html += '<tbody>';

                                examResult.subjects.forEach(function(subject, idx) {
                                    var subGradeOrDivision = '';
                                    var subGradeClass = '';
                                    if (schoolType === 'Primary') {
                                        subGradeOrDivision = subject.division || 'Division Zero';
                                        if (subGradeOrDivision === 'Division One') {
                                            subGradeClass = 'bg-success text-white';
                                        } else if (subGradeOrDivision === 'Division Two') {
                                            subGradeClass = 'bg-info text-white';
                                        } else if (subGradeOrDivision === 'Division Three') {
                                            subGradeClass = 'bg-warning text-dark';
                                        } else if (subGradeOrDivision === 'Division Four') {
                                            subGradeClass = 'bg-danger text-white';
                                        } else {
                                            subGradeClass = 'bg-secondary text-white';
                                        }
                                    } else {
                                        subGradeOrDivision = subject.grade || 'Incomplete';
                                        if (subGradeOrDivision === 'A') {
                                            subGradeClass = 'bg-success text-white';
                                        } else if (subGradeOrDivision === 'F' || subGradeOrDivision === 'E') {
                                            subGradeClass = 'bg-danger text-white';
                                        } else if (subGradeOrDivision === 'Incomplete') {
                                            subGradeClass = 'bg-warning text-dark';
                                        } else {
                                            subGradeClass = 'bg-info text-white';
                                        }
                                    }

                                    html += '<tr>';
                                    html += '<td>' + (idx + 1) + '</td>';
                                    html += '<td>' + (subject.subject_name || 'N/A') + '</td>';
                                    html += '<td>' + (subject.subject_code || 'N/A') + '</td>';
                                    html += '<td><strong>' + (subject.marks !== null ? subject.marks : '-') + '</strong></td>';
                                    html += '<td><span class="badge ' + subGradeClass + '">' + subGradeOrDivision + '</span></td>';
                                    html += '<td>' + (subject.remark || '-') + '</td>';
                                    html += '</tr>';
                                });

                                html += '</tbody>';
                                html += '</table>';
                                html += '</div>';
                                html += '</div>';
                                html += '</div>';
                            });
                        } else {
                            html += '<div class="alert alert-info">No results found for this student.</div>';
                        }

                        $('#moreResultsModalBody').html(html);
                    } else {
                        $('#moreResultsModalBody').html('<div class="alert alert-info">No results found.</div>');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.error || 'Failed to load detailed results', 'error');
                    $('#viewMoreResultsModal').modal('hide');
                }
            });
        });

        // Store student data and exam results globally for PDF generation
        var currentStudentData = null;
        var currentExamResults = null;

        // Download PDF using jsPDF (with images)
        $(document).on('click', '.download-pdf-btn', function() {
            var $button = $(this);
            var examID = $button.data('exam-id');
            var studentID = $button.data('student-id');

            // Check if jsPDF is available
            var jsPDFLib = window.jspdf || window.jsPDF;
            var JSPDF = null;

            if (jsPDFLib && jsPDFLib.jsPDF) {
                JSPDF = jsPDFLib.jsPDF;
            } else if (typeof jsPDF !== 'undefined') {
                JSPDF = jsPDF;
            } else if (typeof window.jsPDF !== 'undefined') {
                JSPDF = window.jsPDF;
            }

            if (!JSPDF) {
                Swal.fire('Error', 'PDF library not loaded. Please refresh the page.', 'error');
                return;
            }

            // Show loading
            Swal.fire({
                title: 'Generating PDF...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Get exam result data from stored data or fetch it
            var examResult = null;
            if (currentExamResults && examID) {
                examResult = currentExamResults.find(function(r) {
                    return r.examID == examID;
                });
            }

            // If not found, try to get from card
            if (!examResult) {
                var $card = $button.closest('.card');
                if ($card.length === 0) {
                    Swal.fire('Error', 'Could not find results card', 'error');
                    Swal.close();
                    return;
                }

                // Extract from card (fallback)
                var $cardBody = $card.find('.card-body');
                var $examHeader = $card.find('.card-header h6');
                var examName = '';
                var examYear = '';
                if ($examHeader.length) {
                    var examText = $examHeader.text();
                    var match = examText.match(/(.+?)\s*\((\d+)\)/);
                    if (match) {
                        examName = match[1];
                        examYear = match[2];
                    }
                }

                examResult = {
                    examID: examID,
                    exam_name: examName,
                    year: examYear,
                    total_marks: 0,
                    average_marks: 0,
                    subject_count: 0,
                    total_division: 'N/A',
                    total_grade: 'N/A',
                    class_position: 'N/A',
                    total_class_students: 0,
                    subjects: []
                };

                // Extract summary
                $cardBody.find('.row').each(function() {
                    var $row = $(this);
                    var text = $row.text();
                    if (text.includes('Total Marks:')) {
                        var match = text.match(/Total Marks:\s*(\d+\.?\d*)/);
                        if (match) examResult.total_marks = parseFloat(match[1]);
                    }
                    if (text.includes('Average:')) {
                        var match = text.match(/Average:\s*(\d+\.?\d*)/);
                        if (match) examResult.average_marks = parseFloat(match[1]);
                    }
                    if (text.includes('Subjects:')) {
                        var match = text.match(/Subjects:\s*(\d+)/);
                        if (match) examResult.subject_count = parseInt(match[1]);
                    }
                    if (text.includes('Position:')) {
                        var match = text.match(/Position:\s*(\d+|N\/A)(?:\s+out of\s+(\d+))?/);
                        if (match) {
                            examResult.class_position = match[1];
                            if (match[2]) examResult.total_class_students = parseInt(match[2]);
                        }
                    }
                });

                // Get grade/division
                var $gradeBadge = $cardBody.find('.badge');
                if ($gradeBadge.length) {
                    examResult.total_division = $gradeBadge.text().trim();
                    examResult.total_grade = examResult.total_division;
                }

                // Get subjects from table
                $cardBody.find('table tbody tr').each(function() {
                    var $row = $(this);
                    var cols = $row.find('td');
                    if (cols.length >= 5) {
                        examResult.subjects.push({
                            number: $(cols[0]).text().trim(),
                            subject_name: $(cols[1]).text().trim(),
                            subject_code: $(cols[2]).text().trim(),
                            marks: $(cols[3]).text().trim(),
                            grade: $(cols[4]).find('.badge').text().trim() || $(cols[4]).text().trim(),
                            remark: $(cols[5]).text().trim()
                        });
                    }
                });
            }

            // Get student info
            var studentName = 'Student';
            var admissionNumber = 'N/A';
            var studentClass = 'N/A';
            var studentPhoto = null;

            if (currentStudentData) {
                studentName = (currentStudentData.first_name || '') + ' ' + (currentStudentData.middle_name || '') + ' ' + (currentStudentData.last_name || '');
                admissionNumber = currentStudentData.admission_number || 'N/A';
                if (currentStudentData.subclass) {
                    studentClass = currentStudentData.subclass.subclass_name || 'N/A';
                }
                studentPhoto = currentStudentData.photo ? '{{ asset("userImages/") }}/' + currentStudentData.photo : null;
            }

            // Get school info
            var schoolName = '{{ $school_details->school_name ?? "School" }}';
            var schoolLogo = '{{ $school_details->school_logo ?? "" }}';
            var logoUrl = schoolLogo ? '{{ asset("logos") }}/' + schoolLogo : null;
            var schoolType = '{{ $school_details->school_type ?? "Secondary" }}';

            try {
                var doc = new JSPDF('p', 'mm', 'a4');
                var pageWidth = doc.internal.pageSize.getWidth();
                var pageHeight = doc.internal.pageSize.getHeight();
                var margin = 15;
                var yPos = margin;
                var lineHeight = 7;

                // Load images (logo and student photo)
                var logoPromise = Promise.resolve(null);
                if (logoUrl) {
                    logoPromise = new Promise(function(resolve) {
                        var img = new Image();
                        img.crossOrigin = 'anonymous';
                        img.onload = function() {
                            resolve(img);
                        };
                        img.onerror = function() {
                            resolve(null);
                        };
                        img.src = logoUrl + (logoUrl.indexOf('?') > -1 ? '&' : '?') + 't=' + Date.now();
                    });
                }

                var photoPromise = Promise.resolve(null);
                if (studentPhoto) {
                    photoPromise = new Promise(function(resolve) {
                        var img = new Image();
                        img.crossOrigin = 'anonymous';
                        img.onload = function() {
                            resolve(img);
                        };
                        img.onerror = function() {
                            resolve(null);
                        };
                        img.src = studentPhoto + (studentPhoto.indexOf('?') > -1 ? '&' : '?') + 't=' + Date.now();
                    });
                }

                Promise.all([logoPromise, photoPromise]).then(function(images) {
                    var logoImg = images[0];
                    var photoImg = images[1];

                    // Header with logo
                    var logoWidth = 0;
                    var logoHeight = 0;
                    var logoX = margin;
                    var logoY = margin;

                    if (logoImg) {
                        try {
                            logoWidth = 25;
                            logoHeight = (logoImg.height / logoImg.width) * logoWidth;
                            // Try different image formats
                            var imgFormat = 'PNG';
                            if (logoUrl && logoUrl.toLowerCase().endsWith('.jpg')) imgFormat = 'JPEG';
                            if (logoUrl && logoUrl.toLowerCase().endsWith('.jpeg')) imgFormat = 'JPEG';
                            doc.addImage(logoImg, imgFormat, logoX, logoY, logoWidth, logoHeight);
                        } catch(e) {
                            console.log('Could not add logo:', e);
                            logoImg = null;
                        }
                    }

                    // School name and title (centered, logo on left)
                    var titleX = pageWidth / 2;
                    var titleY = logoImg ? (margin + logoHeight / 2 - lineHeight * 1.5) : margin;
                    yPos = titleY;

                    doc.setFontSize(14);
                    doc.setFont('helvetica', 'bold');
                    doc.setTextColor(148, 0, 0); // #940000
                    doc.text(String(schoolName).toUpperCase(), titleX, yPos, { align: 'center' });
                    yPos += lineHeight;

                    doc.setFontSize(12);
                    doc.text('STUDENT RESULTS', titleX, yPos, { align: 'center' });
                    yPos += lineHeight * 2;

                    // Adjust yPos if logo extends below title
                    if (logoImg && (logoY + logoHeight) > yPos) {
                        yPos = logoY + logoHeight + lineHeight;
                    }

                    // Student info table with borders and photo (rowspan effect)
                    var infoTableY = yPos;
                    var infoTableWidth = pageWidth - (margin * 2);
                    var cellHeight = 7;
                    var infoTableHeight = cellHeight * 4; // 4 rows
                    var leftColWidth = infoTableWidth * 0.6; // 60% for info
                    var rightColWidth = infoTableWidth * 0.4; // 40% for photo

                    // Draw table borders
                    doc.setDrawColor(0, 0, 0);
                    doc.setLineWidth(0.1);

                    // Outer border
                    doc.rect(margin, infoTableY, infoTableWidth, infoTableHeight);

                    // Vertical line separating info and photo
                    doc.line(margin + leftColWidth, infoTableY, margin + leftColWidth, infoTableY + infoTableHeight);

                    // Horizontal lines (4 rows)
                    for (var i = 1; i < 4; i++) {
                        // Only draw line on left side (info side)
                        doc.line(margin, infoTableY + (cellHeight * i), margin + leftColWidth, infoTableY + (cellHeight * i));
                    }

                    // Student photo (right side, spanning all 4 rows - rowspan effect)
                    var photoWidth = 0;
                    var photoHeight = 0;
                    var photoX = margin + leftColWidth + 2;
                    var photoY = infoTableY + 2;
                    var photoAreaHeight = infoTableHeight - 4;

                    if (photoImg) {
                        try {
                            photoWidth = rightColWidth - 4;
                            photoHeight = (photoImg.height / photoImg.width) * photoWidth;
                            // Fit photo in available space
                            if (photoHeight > photoAreaHeight) {
                                photoHeight = photoAreaHeight;
                                photoWidth = (photoImg.width / photoImg.height) * photoHeight;
                            }
                            // Center photo vertically
                            var photoYCentered = infoTableY + (infoTableHeight / 2) - (photoHeight / 2);
                            doc.addImage(photoImg, 'PNG', photoX, photoYCentered, photoWidth, photoHeight);
                        } catch(e) {
                            console.log('Could not add photo:', e);
                            photoImg = null;
                        }
                    }

                    // Student info table content (left side)
                    doc.setFontSize(9);
                    doc.setFont('helvetica', 'normal');
                    doc.setTextColor(0, 0, 0);

                    var infoTableData = [
                        ['Student Name:', studentName],
                        ['Admission Number:', admissionNumber],
                        ['Class:', studentClass],
                        ['Examination:', (examResult.exam_name || '') + ' (' + (examResult.year || '') + ')']
                    ];

                    var infoTableX = margin + 2;
                    var infoTableStartY = infoTableY + 5;

                    infoTableData.forEach(function(row, idx) {
                        doc.setFont('helvetica', 'bold');
                        var labelWidth = 35;
                        doc.text(String(row[0]), infoTableX, infoTableStartY);
                        doc.setFont('helvetica', 'normal');
                        var valueX = infoTableX + labelWidth;
                        var valueText = String(row[1]);
                        // Truncate if too long
                        var maxWidth = leftColWidth - labelWidth - 4;
                        if (valueText.length > 30) {
                            valueText = valueText.substring(0, 27) + '...';
                        }
                        doc.text(valueText, valueX, infoTableStartY);
                        infoTableStartY += cellHeight;
                    });

                    yPos = infoTableY + infoTableHeight + lineHeight;

                    // Summary/Grades table (before results table) with borders
                    var summaryTableY = yPos;
                    var summaryTableWidth = pageWidth - (margin * 2);
                    var summaryRowHeight = 6;
                    var summaryTableHeight = summaryRowHeight * 3; // 3 rows

                    // Draw summary table borders
                    doc.setDrawColor(0, 0, 0);
                    doc.setLineWidth(0.1);
                    doc.rect(margin, summaryTableY, summaryTableWidth, summaryTableHeight);

                    // Vertical lines (3 columns)
                    var summaryCol1 = margin + (summaryTableWidth / 3);
                    var summaryCol2 = margin + (summaryTableWidth / 3 * 2);
                    doc.line(summaryCol1, summaryTableY, summaryCol1, summaryTableY + summaryTableHeight);
                    doc.line(summaryCol2, summaryTableY, summaryCol2, summaryTableY + summaryTableHeight);

                    // Horizontal lines
                    for (var i = 1; i < 3; i++) {
                        doc.line(margin, summaryTableY + (summaryRowHeight * i), margin + summaryTableWidth, summaryTableY + (summaryRowHeight * i));
                    }

                    // Header row background
                    doc.setFillColor(148, 0, 0); // #940000
                    doc.rect(margin, summaryTableY, summaryTableWidth, summaryRowHeight, 'F');

                    // Summary table content
                    doc.setFontSize(9);
                    doc.setFont('helvetica', 'bold');
                    doc.setTextColor(255, 255, 255);
                    doc.text('SUMMARY', margin + summaryTableWidth / 2, summaryTableY + 4, { align: 'center' });

                    doc.setTextColor(0, 0, 0);
                    doc.setFont('helvetica', 'normal');
                    var summaryY = summaryTableY + summaryRowHeight + 4;

                    // Row 1
                    doc.setFont('helvetica', 'bold');
                    doc.text('Total Marks:', margin + 2, summaryY);
                    doc.setFont('helvetica', 'normal');
                    doc.text(String(examResult.total_marks || 0), summaryCol1 + 2, summaryY);

                    doc.setFont('helvetica', 'bold');
                    doc.text('Average:', summaryCol2 + 2, summaryY);
                    doc.setFont('helvetica', 'normal');
                    doc.text(String((examResult.average_marks || 0).toFixed(2)), margin + summaryTableWidth - 2, summaryY, { align: 'right' });

                    summaryY += summaryRowHeight;

                    // Row 2
                    doc.setFont('helvetica', 'bold');
                    doc.text('Subjects:', margin + 2, summaryY);
                    doc.setFont('helvetica', 'normal');
                    doc.text(String(examResult.subject_count || 0), summaryCol1 + 2, summaryY);

                    doc.setFont('helvetica', 'bold');
                    var gradeLabel = 'Total ' + (schoolType === 'Primary' ? 'Division' : 'Grade') + ':';
                    doc.text(gradeLabel, summaryCol2 + 2, summaryY);
                    doc.setFont('helvetica', 'normal');
                    doc.text(String(examResult.total_division || examResult.total_grade || 'N/A'), margin + summaryTableWidth - 2, summaryY, { align: 'right' });

                    summaryY += summaryRowHeight;

                    // Row 3
                    doc.setFont('helvetica', 'bold');
                    doc.text('Position:', margin + 2, summaryY);
                    doc.setFont('helvetica', 'normal');
                    var positionText = String(examResult.class_position || 'N/A');
                    if (examResult.total_class_students) {
                        positionText = positionText + ' out of ' + examResult.total_class_students;
                    }
                    doc.text(positionText, summaryCol1 + 2, summaryY);

                    // Empty cell for alignment
                    doc.text('', summaryCol2 + 2, summaryY);

                    yPos = summaryTableY + summaryTableHeight + lineHeight;

                    // Results table with borders (same width as other tables)
                    var tableStartY = yPos;
                    var tableWidth = pageWidth - (margin * 2); // Same width as other tables
                    var colWidths = [8, 50, 20, 20, 25, 30];
                    var totalColWidth = colWidths.reduce(function(a, b) { return a + b; }, 0);
                    // Scale column widths to fit table width
                    var scaleFactor = tableWidth / totalColWidth;
                    colWidths = colWidths.map(function(w) { return w * scaleFactor; });
                    var headers = ['#', 'Subject', 'Code', 'Marks', schoolType === 'Primary' ? 'Division' : 'Grade', 'Remark'];
                    var tableX = margin;

                    // Calculate number of rows needed
                    var numRows = (examResult.subjects || []).length;
                    var rowHeight = 6;
                    var tableHeight = rowHeight * (numRows + 1); // +1 for header

                    // Draw table borders
                    doc.setDrawColor(0, 0, 0);
                    doc.setLineWidth(0.1);
                    doc.rect(tableX, tableStartY, tableWidth, tableHeight);

                    // Draw header background
                    doc.setFillColor(148, 0, 0); // #940000
                    doc.rect(tableX, tableStartY, tableWidth, rowHeight, 'F');

                    // Draw vertical lines
                    var xPos = tableX;
                    for (var i = 0; i < colWidths.length; i++) {
                        xPos += colWidths[i];
                        if (i < colWidths.length - 1) {
                            doc.line(xPos, tableStartY, xPos, tableStartY + tableHeight);
                        }
                    }

                    // Draw horizontal lines
                    for (var i = 1; i <= numRows; i++) {
                        doc.line(tableX, tableStartY + (rowHeight * i), tableX + tableWidth, tableStartY + (rowHeight * i));
                    }

                    // Header text
                    doc.setFontSize(9);
                    doc.setFont('helvetica', 'bold');
                    doc.setTextColor(255, 255, 255);
                    xPos = tableX + 2;
                    headers.forEach(function(header, i) {
                        doc.text(String(header), xPos, tableStartY + 4, { align: 'left' });
                        xPos += colWidths[i];
                    });
                    yPos = tableStartY + rowHeight;
                    doc.setTextColor(0, 0, 0);

                    // Table rows
                    doc.setFont('helvetica', 'normal');
                    doc.setFontSize(8);

                    (examResult.subjects || []).forEach(function(subject, index) {
                        // Check if we need a new page
                        if (yPos > pageHeight - 30) {
                            doc.addPage();
                            yPos = margin;
                        }

                        xPos = tableX + 2;
                        doc.text(String(subject.number || (index + 1)), xPos, yPos + 4);
                        xPos += colWidths[0];

                        var subjectName = String(subject.subject_name || subject.name || 'N/A');
                        if (subjectName.length > 20) subjectName = subjectName.substring(0, 17) + '...';
                        doc.text(subjectName, xPos, yPos + 4);
                        xPos += colWidths[1];

                        var subjectCode = String(subject.subject_code || subject.code || 'N/A');
                        if (subjectCode.length > 8) subjectCode = subjectCode.substring(0, 5) + '...';
                        doc.text(subjectCode, xPos, yPos + 4);
                        xPos += colWidths[2];

                        doc.text(String(subject.marks || '-'), xPos, yPos + 4);
                        xPos += colWidths[3];

                        var grade = String(subject.grade || subject.division || '-');
                        if (grade.length > 10) grade = grade.substring(0, 7) + '...';
                        doc.text(grade, xPos, yPos + 4);
                        xPos += colWidths[4];

                        var remark = String(subject.remark || '-');
                        if (remark.length > 15) remark = remark.substring(0, 12) + '...';
                        doc.text(remark, xPos, yPos + 4);

                        yPos += rowHeight;
                    });

                    // Footer
                    var totalPages = doc.internal.getNumberOfPages();
                    for (var i = 1; i <= totalPages; i++) {
                        doc.setPage(i);
                        doc.setFontSize(8);
                        doc.setFont('helvetica', 'italic');
                        doc.setTextColor(100, 100, 100);
                        doc.text('Generated on: ' + new Date().toLocaleDateString('en-GB') + ' ' + new Date().toLocaleTimeString('en-GB'), pageWidth / 2, pageHeight - 10, { align: 'center' });
                        doc.setFont('helvetica', 'bold');
                        doc.setTextColor(148, 0, 0); // #940000
                        doc.text('Powered by: EmCa Technologies LTD', pageWidth / 2, pageHeight - 5, { align: 'center' });
                    }

                    // Save PDF
                    var filename = 'student_results_' + admissionNumber.replace(/\s+/g, '_') + '_' + (examResult.exam_name || 'exam').replace(/\s+/g, '_') + '.pdf';
                    doc.save(filename);

                    Swal.close();
                }).catch(function(error) {
                    console.error('PDF generation error:', error);
                    Swal.fire('Error', 'Failed to generate PDF: ' + error.message, 'error');
                });
            } catch (error) {
                console.error('PDF generation error:', error);
                Swal.fire('Error', 'Failed to generate PDF: ' + error.message, 'error');
            }
        });

        // Generate PDF using jsPDF
        function generateParentsPDF(data) {
            // Check if jsPDF is available
            var jsPDFLib = window.jspdf || window.jsPDF;
            var JSPDF = null;

            if (jsPDFLib && jsPDFLib.jsPDF) {
                JSPDF = jsPDFLib.jsPDF;
            } else if (typeof jsPDF !== 'undefined') {
                JSPDF = jsPDF;
            } else if (typeof window.jsPDF !== 'undefined') {
                JSPDF = window.jsPDF;
            }

            if (!JSPDF) {
                Swal.fire('Error', 'PDF library not loaded. Please refresh the page.', 'error');
                return;
            }

            try {
                var doc = new JSPDF('p', 'mm', 'a4');
                var pageWidth = doc.internal.pageSize.getWidth();
                var pageHeight = doc.internal.pageSize.getHeight();
                var margin = 15;
                var yPos = margin;
                var lineHeight = 7;

                // Load school logo if available
                var logoPromise = Promise.resolve(null);
                if (data.school.school_logo) {
                    logoPromise = new Promise(function(resolve) {
                        var img = new Image();
                        img.crossOrigin = 'anonymous';
                        img.onload = function() {
                            resolve(img);
                        };
                        img.onerror = function() {
                            console.log('Logo load error, continuing without logo');
                            resolve(null);
                        };
                        // Add timestamp to avoid cache issues
                        img.src = data.school.school_logo + (data.school.school_logo.indexOf('?') > -1 ? '&' : '?') + 't=' + Date.now();
                    });
                }

                logoPromise.then(function(logoImg) {
                    // Header with logo on the left side
                    var logoWidth = 0;
                    var logoHeight = 0;
                    var logoX = margin;
                    var logoY = margin;

                    if (logoImg) {
                        try {
                            logoWidth = 25;
                            logoHeight = (logoImg.height / logoImg.width) * logoWidth;
                            // Position logo on the left side
                            doc.addImage(logoImg, 'PNG', logoX, logoY, logoWidth, logoHeight);
                        } catch(e) {
                            console.log('Could not add logo:', e);
                            logoImg = null;
                        }
                    }

                    // Title - centered, logo on left side
                    var titleX = pageWidth / 2;
                    var titleY = logoImg ? (margin + logoHeight / 2 - lineHeight * 2) : margin;
                    yPos = titleY;

                    doc.setFontSize(14);
                    doc.setFont('helvetica', 'bold');
                    doc.text('JAMUHURI YA MUUNGANO WA TANZANIA', titleX, yPos, { align: 'center' });
                    yPos += lineHeight;

                    doc.setFontSize(12);
                    doc.setFont('helvetica', 'normal');
                    doc.text('TAWALA ZA MIKOA NA SERIKALI ZA MITAA', titleX, yPos, { align: 'center' });
                    yPos += lineHeight;

                    doc.setFont('helvetica', 'bold');
                    doc.text('TAMISEMI ' + data.school.school_name.toUpperCase(), titleX, yPos, { align: 'center' });
                    yPos += lineHeight;

                    doc.text('WAZAZI WA DARASA ' + data.subclassName.toUpperCase(), titleX, yPos, { align: 'center' });
                    yPos += lineHeight * 2;

                    // Adjust yPos if logo extends below title
                    if (logoImg && (logoY + logoHeight) > yPos) {
                        yPos = logoY + logoHeight + lineHeight;
                    }

                    // Table headers
                    doc.setFontSize(10);
                    doc.setFont('helvetica', 'bold');
                    var tableStartY = yPos;
                    var colWidths = [10, 50, 30, 35, 30, 35];
                    var headers = ['#', 'Jina la Mzazi', 'Namba ya Simu', 'Barua Pepe', 'Kazi', 'Wanafunzi'];
                    var xPos = margin;

                    // Draw header background
                    doc.setFillColor(148, 0, 0); // #940000
                    doc.rect(margin, tableStartY - 5, pageWidth - (margin * 2), 8, 'F');

                    // Header text
                    doc.setTextColor(255, 255, 255);
                    for (var i = 0; i < headers.length; i++) {
                        doc.text(headers[i], xPos, tableStartY, { align: 'left' });
                        xPos += colWidths[i];
                    }
                    yPos = tableStartY + 8;
                    doc.setTextColor(0, 0, 0);

                    // Table rows
                    doc.setFont('helvetica', 'normal');
                    doc.setFontSize(9);

                    if (data.parents && data.parents.length > 0) {
                        data.parents.forEach(function(parent, index) {
                            // Check if we need a new page
                            if (yPos > pageHeight - 30) {
                                doc.addPage();
                                yPos = margin;
                            }

                            var fullName = parent.first_name + (parent.middle_name ? ' ' + parent.middle_name : '') + ' ' + parent.last_name;
                            var studentsList = '';
                            if (parent.students && parent.students.length > 0) {
                                studentsList = parent.students.map(function(s) {
                                    return s.full_name + ' (' + s.admission_number + ')';
                                }).join(', ');
                            } else {
                                studentsList = 'N/A';
                            }

                            xPos = margin;
                            doc.text((index + 1).toString(), xPos, yPos);
                            xPos += colWidths[0];

                            doc.text(fullName.substring(0, 25), xPos, yPos);
                            xPos += colWidths[1];

                            doc.text(parent.phone || 'N/A', xPos, yPos);
                            xPos += colWidths[2];

                            doc.text((parent.email || 'N/A').substring(0, 20), xPos, yPos);
                            xPos += colWidths[3];

                            doc.text((parent.occupation || 'N/A').substring(0, 15), xPos, yPos);
                            xPos += colWidths[4];

                            // Students list - may need to wrap
                            var studentsLines = doc.splitTextToSize(studentsList, colWidths[5]);
                            doc.text(studentsLines[0], xPos, yPos);

                            yPos += Math.max(lineHeight, studentsLines.length * lineHeight);
                        });
                    } else {
                        doc.text('Hakuna wazazi walioorodheshwa', pageWidth / 2, yPos, { align: 'center' });
                    }

                    // Footer
                    var totalPages = doc.internal.getNumberOfPages();
                    for (var i = 1; i <= totalPages; i++) {
                        doc.setPage(i);
                        doc.setFontSize(8);
                        doc.setFont('helvetica', 'italic');
                        doc.setTextColor(100, 100, 100);
                        doc.text('Powered by: EmCa Technologies LTD', pageWidth / 2, pageHeight - 10, { align: 'center' });
                        doc.text('Generated on: ' + new Date().toLocaleDateString('en-GB') + ' ' + new Date().toLocaleTimeString('en-GB'), pageWidth / 2, pageHeight - 5, { align: 'center' });
                    }

                    // Save PDF
                    var filename = 'Wazazi_Darasa_' + data.subclassName.replace(/\s+/g, '_') + '_' + new Date().toISOString().split('T')[0] + '.pdf';
                    doc.save(filename);
                });
            } catch (error) {
                console.error('PDF generation error:', error);
                Swal.fire('Error', 'Failed to generate PDF: ' + error.message, 'error');
            }
        }

        // ==================== ATTENDANCE MANAGEMENT ====================
        
        // Format date function (e.g., "11 November 2025")
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            
            try {
                var date = new Date(dateString);
                if (isNaN(date.getTime())) {
                    // If dateString is already formatted, try to parse it
                    var parts = dateString.split('-');
                    if (parts.length === 3) {
                        date = new Date(parts[0], parts[1] - 1, parts[2]);
                    } else {
                        return dateString; // Return as is if can't parse
                    }
                }
                
                var months = ['January', 'February', 'March', 'April', 'May', 'June',
                             'July', 'August', 'September', 'October', 'November', 'December'];
                
                var day = date.getDate();
                var month = months[date.getMonth()];
                var year = date.getFullYear();
                
                return day + ' ' + month + ' ' + year;
            } catch (e) {
                return dateString; // Return original if error
            }
        }
        
        // Attendance Button Click
        $('#attendanceBtn').on('click', function() {
            if (isCoordinatorView && classID) {
                // Coordinator view - redirect to manage_attendance blade
                window.location.href = '{{ route("manageAttendance") }}?classID=' + classID + '&coordinator=true';
            } else if (subclassID) {
                // Class teacher view - show modal
                $('#attendanceModal').modal('show');
                loadStudentsForAttendance();
            } else {
                Swal.fire('Error', 'Class ID not found', 'error');
                return;
            }
            
            // Set date to today and lock it
            var today = new Date().toISOString().split('T')[0];
            $('#attendance_date').val(today).attr('max', today).attr('min', today);
        });

        // Prevent date change - always keep it as today
        $('#attendance_date').on('change', function() {
            var today = new Date().toISOString().split('T')[0];
            if ($(this).val() !== today) {
                $(this).val(today);
                Swal.fire('Info', 'Only today\'s date is allowed for attendance collection', 'info');
            }
        });

        // Load Students for Attendance Collection
        // Load Subclasses for Attendance (Coordinator View)
        function loadSubclassesForAttendance() {
            if (!classID) {
                Swal.fire('Error', 'Class ID not found', 'error');
                return;
            }
            
            $.ajax({
                url: '{{ url("get_class_subclasses") }}/' + classID,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var $subclassSelect = $('#attendanceSubclassSelect');
                    $subclassSelect.empty();
                    $subclassSelect.append('<option value="">Select Subclass</option>');
                    
                    if (response.subclasses && response.subclasses.length > 0) {
                        $.each(response.subclasses, function(index, subclass) {
                            var displayName = subclass.subclass_name 
                                ? subclass.class_name + ' - ' + subclass.subclass_name 
                                : subclass.class_name;
                            $subclassSelect.append('<option value="' + subclass.subclassID + '">' + displayName + '</option>');
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading subclasses for attendance:', error);
                    Swal.fire('Error', 'Failed to load subclasses', 'error');
                }
            });
            
            // When subclass is selected, load students
            $('#attendanceSubclassSelect').off('change').on('change', function() {
                var selectedSubclassID = $(this).val();
                if (selectedSubclassID) {
                    // Update global subclassID for consistency
                    if (isCoordinatorView) {
                        subclassID = selectedSubclassID;
                    }
                    loadStudentsForAttendance(selectedSubclassID);
                } else {
                    $('#attendanceStudentsList').html('<tr><td colspan="6" class="text-center text-muted">Please select a subclass</td></tr>');
                    if (isCoordinatorView) {
                        subclassID = null;
                    }
                }
            });
        }
        
        function loadStudentsForAttendance(subclassIDParam) {
            var targetSubclassID = subclassIDParam || subclassID;
            
            if (!targetSubclassID) {
                $('#attendanceStudentsList').html('<tr><td colspan="6" class="text-center text-warning">Please select a class first</td></tr>');
                return;
            }

            var attendanceDate = $('#attendance_date').val();

            $.ajax({
                url: '{{ url("get_subclass_students") }}/' + targetSubclassID,
                type: 'GET',
                data: {
                    attendance_date: attendanceDate,
                    coordinator: isCoordinatorView ? 'true' : 'false',
                    classID: typeof classID !== 'undefined' ? classID : null
                },
                dataType: 'json',
                success: function(response) {
                    var tbody = $('#attendanceStudentsList');
                    tbody.empty();

                    if (response.success && response.students && response.students.length > 0) {
                        var baseUrl = '{{ asset("") }}';
                        var studentIndex = 0;
                        
                        $.each(response.students, function(index, student) {
                            // Skip transferred students
                            if (student.status === 'Transferred') return;

                            var photoUrl = '';
                            var firstLetter = (student.first_name || 'N').charAt(0).toUpperCase();
                            var placeholderColor = '#940000';

                            if (student.photo) {
                                photoUrl = baseUrl + 'userImages/' + student.photo;
                            }

                            var photoHtml = '';
                            if (photoUrl) {
                                photoHtml = '<div style="position: relative; display: inline-block;">' +
                                    '<img src="' + photoUrl + '" alt="Student Photo" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #940000; cursor: pointer;" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">' +
                                    '<div class="rounded-circle d-none align-items-center justify-content-center text-white" style="width: 40px; height: 40px; background-color: ' + placeholderColor + '; font-size: 16px; font-weight: bold; border: 2px solid #940000;">' + firstLetter + '</div>' +
                                    '</div>';
                            } else {
                                photoHtml = '<div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px; background-color: ' + placeholderColor + '; font-size: 16px; font-weight: bold; border: 2px solid #940000;">' + firstLetter + '</div>';
                            }

                            var status = student.attendance_status || 'Present';
                            var remark = student.attendance_remark || '';

                            var row = '<tr>' +
                                '<td>' + (studentIndex + 1) + '</td>' +
                                '<td>' + photoHtml + '</td>' +
                                '<td><strong>' + (student.admission_number || 'N/A') + '</strong></td>' +
                                '<td>' + (student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '') + '</td>' +
                                '<td>' +
                                    '<select class="form-control form-control-sm attendance-status" name="attendance[' + student.studentID + '][status]" data-student-id="' + student.studentID + '">' +
                                        '<option value="Present"' + (status === 'Present' ? ' selected' : '') + '>Present</option>' +
                                        '<option value="Absent"' + (status === 'Absent' ? ' selected' : '') + '>Absent</option>' +
                                        '<option value="Sick"' + (status === 'Sick' ? ' selected' : '') + '>Sick</option>' +
                                        '<option value="Excused"' + (status === 'Excused' ? ' selected' : '') + '>Permission</option>' +
                                    '</select>' +
                                '</td>' +
                                '<td>' +
                                    '<input type="text" class="form-control form-control-sm" name="attendance[' + student.studentID + '][remark]" placeholder="Optional remark" value="' + remark + '">' +
                                    '<input type="hidden" name="attendance[' + student.studentID + '][studentID]" value="' + student.studentID + '">' +
                                '</td>' +
                            '</tr>';
                            tbody.append(row);
                            studentIndex++;
                        });
                        
                        if (studentIndex === 0) {
                            tbody.append('<tr><td colspan="6" class="text-center text-muted">No active students found</td></tr>');
                        }
                    } else {
                        tbody.append('<tr><td colspan="6" class="text-center text-muted">No active students found</td></tr>');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading students for attendance:', xhr);
                    var errorMsg = 'Error loading students';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.status === 404) {
                        errorMsg = 'Class not found';
                    } else if (xhr.status === 500) {
                        errorMsg = 'Server error. Please try again.';
                    }
                    $('#attendanceStudentsList').html('<tr><td colspan="6" class="text-center text-danger">' + errorMsg + '</td></tr>');
                }
            });
        }

        // Mark All Present
        $('#markAllPresent').on('click', function() {
            $('.attendance-status').val('Present');
        });

        // Mark All Absent
        $('#markAllAbsent').on('click', function() {
            $('.attendance-status').val('Absent');
        });

        // Collect Attendance Form Submit
        $('#collectAttendanceForm').on('submit', function(e) {
            e.preventDefault();
            
            // For coordinator view, ensure subclass is selected
            if (isCoordinatorView && classID) {
                var selectedSubclass = $('#attendanceSubclassSelect').val();
                if (!selectedSubclass) {
                    Swal.fire('Error', 'Please select a subclass', 'error');
                    return;
                }
            }

            var formData = $(this).serialize();
            var submitBtn = $(this).find('button[type="submit"]');
            var originalText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: '{{ route("save_attendance") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(response) {
                    submitBtn.prop('disabled', false).html(originalText);
                    if (response.success) {
                        Swal.fire('Success', response.message || 'Attendance saved successfully', 'success')
                            .then(function() {
                                // Switch to collected attendance tab
                                $('#collected-attendance-tab').tab('show');
                                loadCollectedAttendance();
                            });
                    } else {
                        Swal.fire('Error', response.message || 'Failed to save attendance', 'error');
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html(originalText);
                    var errorMsg = 'Network error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        });

        // Load Collected Attendance
        function loadCollectedAttendance(date, status) {
            if (!subclassID) return;

            var data = {
                subclassID: subclassID
            };
            if (date) data.date = date;
            if (status) data.status = status;

            $.ajax({
                url: '{{ route("get_attendance") }}',
                type: 'GET',
                data: data,
                dataType: 'json',
                success: function(response) {
                    var tbody = $('#collectedAttendanceList');
                    tbody.empty();

                    if (response.success && response.attendances && response.attendances.length > 0) {
                        var baseUrl = '{{ asset("") }}';
                        
                        $.each(response.attendances, function(index, attendance) {
                            var photoUrl = '';
                            var firstLetter = (attendance.first_name || 'N').charAt(0).toUpperCase();
                            var placeholderColor = '#940000';

                            if (attendance.photo) {
                                photoUrl = baseUrl + 'userImages/' + attendance.photo;
                            }

                            var photoHtml = '';
                            if (photoUrl) {
                                photoHtml = '<div style="position: relative; display: inline-block;">' +
                                    '<img src="' + photoUrl + '" alt="Student Photo" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #940000; cursor: pointer;" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">' +
                                    '<div class="rounded-circle d-none align-items-center justify-content-center text-white" style="width: 40px; height: 40px; background-color: ' + placeholderColor + '; font-size: 16px; font-weight: bold; border: 2px solid #940000;">' + firstLetter + '</div>' +
                                    '</div>';
                            } else {
                                photoHtml = '<div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px; background-color: ' + placeholderColor + '; font-size: 16px; font-weight: bold; border: 2px solid #940000;">' + firstLetter + '</div>';
                            }

                            var statusBadge = '';
                            if (attendance.status === 'Present') {
                                statusBadge = '<span class="badge badge-success">Present</span>';
                            } else if (attendance.status === 'Absent') {
                                statusBadge = '<span class="badge badge-danger">Absent</span>';
                            } else if (attendance.status === 'Late') {
                                statusBadge = '<span class="badge badge-warning">Late</span>';
                            } else {
                                statusBadge = '<span class="badge badge-info">Excused</span>';
                            }

                            // Format attendance date
                            var formattedDate = formatDate(attendance.attendance_date);
                            
                            var row = '<tr>' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td>' + photoHtml + '</td>' +
                                '<td><strong>' + (attendance.admission_number || 'N/A') + '</strong></td>' +
                                '<td>' + (attendance.first_name || '') + ' ' + (attendance.middle_name || '') + ' ' + (attendance.last_name || '') + '</td>' +
                                '<td>' + formattedDate + '</td>' +
                                '<td>' + statusBadge + '</td>' +
                                '<td>' + (attendance.remark || '-') + '</td>' +
                                '<td>' +
                                    '<button class="btn btn-sm btn-warning edit-attendance-btn" data-attendance-id="' + attendance.attendanceID + '" title="Edit"><i class="bi bi-pencil-square"></i></button>' +
                                '</td>' +
                            '</tr>';
                            tbody.append(row);
                        });
                    } else {
                        tbody.append('<tr><td colspan="8" class="text-center text-muted">No attendance records found</td></tr>');
                    }
                },
                error: function(xhr) {
                    $('#collectedAttendanceList').html('<tr><td colspan="8" class="text-center text-danger">Error loading attendance</td></tr>');
                }
            });
        }

        // ==================== FINGERPRINT ATTENDANCE (EXTERNAL) ====================

        // When Fingerprint Attendance tab is shown, load data
        $('a#fingerprint-attendance-tab').on('shown.bs.tab', function () {
            loadFingerprintAttendanceTeacher();
        });

        // Sync All from Device button
        $('#syncAllFingerprintAttendance').on('click', function() {
            if (!subclassID) {
                Swal.fire('Error', 'Class ID not found', 'error');
                return;
            }

            const btn = $(this);
            const originalText = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Syncing...');

            $.ajax({
                url: '{{ route("zkteco.attendance.sync-all") }}',
                type: 'POST',
                data: {
                    subclassID: subclassID,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function(response) {
                    btn.prop('disabled', false).html(originalText);
                    
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sync Complete',
                            html: response.message || `Successfully synced ${response.total || 0} attendance record(s) from device.`,
                            timer: 3000,
                            showConfirmButton: true
                        }).then(function() {
                            // Reload attendance after sync
                            loadFingerprintAttendanceTeacher();
                        });
                    } else {
                        Swal.fire('Error', response.message || 'Failed to sync attendance from device', 'error');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(originalText);
                    let errorMsg = 'Network error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        });

        // Refresh button inside fingerprint tab
        $('#refreshFingerprintAttendance').on('click', function() {
            loadFingerprintAttendanceTeacher();
        });

        // Filter by date change
        $('#fingerprintAttendanceDateFilter').on('change', function() {
            loadFingerprintAttendanceTeacher();
        });

        // ==================== LIVE FINGERPRINT ATTENDANCE (TODAY) ====================

        var liveFingerprintInterval = null;

        function renderLiveFingerprintAttendance(records, date, deviceFailed = false) {
            const container = $('#liveFingerprintAttendanceContentTeacher');

            if (!records || records.length === 0) {
                container.html(`
                    <div class="alert ${deviceFailed ? 'alert-warning' : 'alert-info'} mb-0">
                        <i class="bi bi-${deviceFailed ? 'exclamation-triangle' : 'info-circle'}"></i>
                        ${deviceFailed
                            ? 'Device is currently unavailable. Showing no attendance records for today from database.'
                            : 'No attendance records for today yet. Ask students to punch on the biometric device to see live updates.'}
                    </div>
                `);
                return;
            }

            function formatTimeOnly(datetime) {
                if (!datetime) return '';
                const parts = datetime.split(' ');
                if (parts.length === 2) {
                    return parts[1];
                }
                return datetime;
            }

            let html = `
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm mb-0">
                        <thead class="bg-primary-custom text-white">
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Class</th>
                                <th>Fingerprint ID</th>
                                <th>Check In Time</th>
                                <th>Check Out Time</th>
                                <th>Status</th>
                                <th>Verify Mode</th>
                                <th>Device IP</th>
                                <th>New</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            $.each(records, function(index, rec) {
                const fullName = rec.user_name || 'N/A';
                const className = rec.class_name || 'N/A';
                const fingerprintId = rec.fingerprint_id || rec.enroll_id || rec.user_id || 'N/A';
                const checkInTime = formatTimeOnly(rec.check_in_time || '');
                const checkOutTime = formatTimeOnly(rec.check_out_time || '');
                const verifyMode = rec.verify_mode || 'Fingerprint';
                const deviceIp = rec.device_ip || 'N/A';
                const isNew = rec.is_new === true;

                // New badge (if record is new) - will be in last column
                const newBadge = isNew 
                    ? '<span class="badge bg-success">New</span>' 
                    : '-';

                // Check In badge (success/green)
                const checkInBadge = checkInTime 
                    ? '<span class="badge bg-success">Check In</span>' 
                    : '';

                // Check Out badge (primary/blue)
                const checkOutBadge = checkOutTime 
                    ? '<span class="badge bg-primary">Check Out</span>' 
                    : '';

                // Status (Complete or Check In Only)
                const status = rec.status === 'Complete' || rec.status === 1
                    ? '<span class="badge bg-success">Complete</span>'
                    : '<span class="badge bg-info">Check In Only</span>';

                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${fullName}</td>
                        <td>${className}</td>
                        <td>${fingerprintId}</td>
                        <td>${checkInTime || '-'} ${checkInBadge}</td>
                        <td>${checkOutTime || '-'} ${checkOutBadge}</td>
                        <td>${status}</td>
                        <td>${verifyMode}</td>
                        <td>${deviceIp}</td>
                        <td>${newBadge}</td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 small text-muted">
                    Date: ${date} &bullet; Source: ${deviceFailed ? 'Database (device unavailable)' : 'Device + Database (synced live)'}
                </div>
            `;

            container.html(html);
        }

        function loadLiveFingerprintAttendance(showLoader = true) {
            const container = $('#liveFingerprintAttendanceContentTeacher');

            // Only show loader on initial load, not on auto-refresh
            if (showLoader) {
                container.html(`
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary-custom" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2 mb-0">Syncing live attendance from device (192.168.1.108) for today...</p>
                    </div>
                `);
            }

            // Add subtle loading indicator for background updates
            if (!showLoader && container.find('.alert, table').length > 0) {
                // Add subtle indicator that update is in progress
                const statusBadge = $('#liveStatusBadge');
                if (statusBadge.length) {
                    statusBadge.find('i').addClass('pulse-animation');
                }
            }

            $.ajax({
                url: '{{ route("zkteco.attendance.live-today") }}',
                type: 'GET',
                data: {
                    subclassID: subclassID
                },
                dataType: 'json',
                timeout: 8000, // 8 second timeout for faster failure detection
                success: function(response) {
                    // Remove pulse animation
                    const statusBadge = $('#liveStatusBadge');
                    if (statusBadge.length) {
                        statusBadge.find('i').removeClass('pulse-animation');
                    }

                    if (!response.success) {
                        if (showLoader) {
                            container.html(`
                                <div class="alert alert-danger mb-0">
                                    <i class="bi bi-exclamation-triangle"></i> ${response.message || 'Failed to load live attendance.'}
                                </div>
                            `);
                        }
                        return;
                    }

                    const records = response.data || [];
                    const date = response.date || '{{ date('Y-m-d') }}';
                    const deviceFailed = !!response.device_failed;

                    renderLiveFingerprintAttendance(records, date, deviceFailed);
                },
                error: function(xhr, status, error) {
                    // Remove pulse animation
                    const statusBadge = $('#liveStatusBadge');
                    if (statusBadge.length) {
                        statusBadge.find('i').removeClass('pulse-animation');
                    }

                    // Only show error on initial load, not on background updates
                    if (showLoader) {
                        console.error('Error loading live fingerprint attendance:', xhr.responseText);
                        container.html(`
                            <div class="alert alert-danger mb-0">
                                <i class="bi bi-exclamation-triangle"></i> Error loading live attendance for today. ${status === 'timeout' ? 'Request timed out.' : ''}
                            </div>
                        `);
                    } else {
                        // Silently fail on background updates to avoid disrupting user experience
                        console.warn('Background sync failed:', error);
                    }
                }
            });
        }

        // Tab show: Auto-start live polling when user opens Live Attendance tab
        $('a#live-fingerprint-attendance-tab').on('shown.bs.tab', function () {
            // Load immediately
            loadLiveFingerprintAttendance(true);

            // Clear any existing interval
            if (liveFingerprintInterval) {
                clearInterval(liveFingerprintInterval);
            }

            // Auto-start polling every 2 seconds for faster updates
            liveFingerprintInterval = setInterval(function() {
                loadLiveFingerprintAttendance(false);
            }, 2000);

            // Update status badge
            $('#liveStatusBadge').removeClass('bg-secondary').addClass('bg-success');
        });

        // Tab hide: Stop polling when user switches to another tab
        $('a#live-fingerprint-attendance-tab').on('hidden.bs.tab', function () {
            if (liveFingerprintInterval) {
                clearInterval(liveFingerprintInterval);
                liveFingerprintInterval = null;
            }
            $('#liveStatusBadge').removeClass('bg-success').addClass('bg-secondary');
        });

        // Stop live polling (manual stop button)
        $('#stopLiveFingerprintAttendance').on('click', function() {
            if (liveFingerprintInterval) {
                clearInterval(liveFingerprintInterval);
                liveFingerprintInterval = null;
            }

            $('#liveStatusBadge').removeClass('bg-success').addClass('bg-secondary');

            Swal.fire({
                icon: 'info',
                title: 'Live attendance stopped',
                text: 'Live syncing has been paused. Switch to another tab and back to resume.',
                timer: 2000,
                showConfirmButton: false
            });
        });

        // Manual refresh
        $('#refreshLiveFingerprintAttendance').on('click', function() {
            loadLiveFingerprintAttendance(true);
        });


        // ==================== FINGERPRINT ATTENDANCE OVERVIEW ====================

        // Globals for fingerprint overview (for export)
        var currentFingerprintFilteredRecords = [];
        var currentFingerprintSearchType = '';
        var currentFingerprintSearchMonth = null;
        var currentFingerprintSearchYear = null;
        var currentFingerprintTotalStudents = 0;
        var currentSubclassStudents = [];

        function toggleFingerprintSearchPickers() {
            var searchType = $('#fingerprintOverviewSearchType').val();
            $('#fingerprintMonthPickerContainer, #fingerprintYearPickerContainer, #fingerprintDayPickerContainer').hide();

            if (searchType === 'month') {
                $('#fingerprintMonthPickerContainer').show();
                $('#exportStudentFingerprintExcelBtn, #exportStudentFingerprintPdfBtn')
                    .show()
                    .prop('disabled', false)
                    .css('pointer-events', 'auto')
                    .css('opacity', '1')
                    .css('cursor', 'pointer');
            } else if (searchType === 'year') {
                $('#fingerprintYearPickerContainer').show();
                $('#exportStudentFingerprintExcelBtn, #exportStudentFingerprintPdfBtn')
                    .show()
                    .prop('disabled', false)
                    .css('pointer-events', 'auto')
                    .css('opacity', '1')
                    .css('cursor', 'pointer');
            } else {
                $('#fingerprintDayPickerContainer').show();
                $('#exportStudentFingerprintExcelBtn, #exportStudentFingerprintPdfBtn')
                    .show()
                    .prop('disabled', false)
                    .css('pointer-events', 'auto')
                    .css('opacity', '1')
                    .css('cursor', 'pointer');
            }
        }

        // Initialize fingerprint pickers on page load
        toggleFingerprintSearchPickers();
        $('#fingerprintOverviewSearchType').on('change', function() {
            toggleFingerprintSearchPickers();
        });
        // Generate Fingerprint Attendance Overview
        $('#generateFingerprintOverviewBtn').on('click', function() {
            var searchType = $('#fingerprintOverviewSearchType').val();
            var searchDate = null;
            var searchMonth = null;
            var searchYear = null;

            if (searchType === 'month') {
                searchMonth = $('#fingerprintOverviewMonth').val();
                if (!searchMonth) {
                    Swal.fire('Error', 'Please select a month', 'error');
                    return;
                }
                searchDate = searchMonth + '-01';
            } else if (searchType === 'year') {
                searchYear = $('#fingerprintOverviewYear').val();
                if (!searchYear) {
                    Swal.fire('Error', 'Please select a year', 'error');
                    return;
                }
                searchDate = searchYear + '-01-01';
            } else {
                searchDate = $('#fingerprintOverviewSearchDate').val();
            if (!searchDate) {
                Swal.fire('Error', 'Please select a date', 'error');
                return;
                }
            }

            $('#fingerprintAttendanceOverviewContent').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"></div></div>');

            // Load all attendance records from database and process them
            loadFingerprintAttendanceOverview(searchType, searchDate, searchMonth, searchYear);
        });

        // Load Fingerprint Attendance Overview from Database
        function loadFingerprintAttendanceOverview(searchType, searchDate, searchMonth, searchYear) {
            if (!subclassID) {
                Swal.fire('Error', 'Class ID not found', 'error');
                return;
            }

            // Show loading message
            $('#fingerprintAttendanceOverviewContent').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 mb-0">Loading attendance from database...</p>
                </div>
            `);

            // First, get total students in the class
            $.ajax({
                url: '{{ url("get_subclass_students") }}/' + subclassID,
                type: 'GET',
                dataType: 'json',
                success: function(studentsResponse) {
                    var totalStudentsInClass = 0;
                    if (studentsResponse.students && Array.isArray(studentsResponse.students)) {
                        totalStudentsInClass = studentsResponse.students.length;
                        currentSubclassStudents = studentsResponse.students;
                    } else {
                        currentSubclassStudents = [];
                    }

                    // Load from database table
                    loadFromDatabase(searchType, searchDate, totalStudentsInClass, searchMonth, searchYear);
                },
                error: function(xhr) {
                    // If we can't get total students, still process with 0
                    loadFromDatabase(searchType, searchDate, 0, searchMonth, searchYear);
                }
            });
        }

        // Load from Database Table
        function loadFromDatabase(searchType, searchDate, totalStudentsInClass, searchMonth = null, searchYear = null) {
            var allRecords = [];
            var currentPage = 1;

            function fetchPage(page) {
                // Build request data based on search type
                var requestData = {
                    page: page,
                    subclassID: subclassID
                };
                
                // For day view, send date to backend for filtering
                if (searchType === 'day' && searchDate) {
                    requestData.date = searchDate;
                } else if (searchType === 'month' && searchMonth) {
                    // Send month parameter (YYYY-MM format)
                    requestData.month = searchMonth;
                } else if (searchType === 'year' && searchYear) {
                    // Send year parameter
                    requestData.year = searchYear;
                }
                
                $.ajax({
                    url: '{{ route("zkteco.attendance.from-db") }}',
                    type: 'GET',
                    data: requestData,
                    dataType: 'json',
                    success: function(data) {
                        if (data.success && data.data) {
                            allRecords = allRecords.concat(data.data);
                            
                            if (data.pagination && data.pagination.current_page < data.pagination.last_page) {
                                fetchPage(page + 1);
                            } else {
                                // All pages loaded, now process the data with total students count
                                // Data always comes from database
                                processFingerprintOverview(allRecords, searchType, searchDate, totalStudentsInClass, true, searchMonth, searchYear);
                            }
                        } else {
                            $('#fingerprintAttendanceOverviewContent').html(`<div class="alert alert-danger">Failed to load attendance data from database</div>`);
                        }
                    },
                    error: function(xhr) {
                        $('#fingerprintAttendanceOverviewContent').html('<div class="alert alert-danger">Error loading attendance data from database.</div>');
                    }
                });
            }

            fetchPage(1);
        }

        // Process Fingerprint Attendance Overview Data
        function processFingerprintOverview(records, searchType, searchDate, totalStudentsInClass, deviceFailed = false, searchMonth = null, searchYear = null) {
            // Filter records by date based on searchType
            var filteredRecords = filterRecordsByDate(records, searchType, searchDate, searchMonth, searchYear);

            // Store for export
            currentFingerprintFilteredRecords = filteredRecords;
            currentFingerprintSearchType = searchType;
            currentFingerprintSearchMonth = searchMonth;
            currentFingerprintSearchYear = searchYear;
            currentFingerprintTotalStudents = totalStudentsInClass;

            // Calculate statistics
            var stats = calculateFingerprintStats(filteredRecords, totalStudentsInClass, searchType, searchMonth, searchYear);

            // Display overview
            displayFingerprintAttendanceOverview(stats, searchType, deviceFailed, filteredRecords, totalStudentsInClass, searchMonth, searchYear);

            // Generate charts
            generateFingerprintAttendanceCharts(filteredRecords, stats, searchType, totalStudentsInClass, searchMonth, searchYear);
        }

        // Filter Records by Date
        function filterRecordsByDate(records, searchType, searchDate, searchMonth = null, searchYear = null) {
            var filtered = [];

            records.forEach(function(rec) {
                if (!rec.attendance_date) return;

                // Normalize attendance_date to YYYY-MM-DD format
                var attendanceDateStr = rec.attendance_date;
                if (attendanceDateStr.indexOf(' ') !== -1) {
                    attendanceDateStr = attendanceDateStr.split(' ')[0];
                }
                if (attendanceDateStr.indexOf('T') !== -1) {
                    attendanceDateStr = attendanceDateStr.split('T')[0];
                }

                var recordDate = new Date(attendanceDateStr);
                var match = false;

                if (searchType === 'day') {
                    var searchDateObj = new Date(searchDate);
                    // Compare date strings (YYYY-MM-DD) to avoid timezone issues
                    var recordDateStr = attendanceDateStr;
                    var searchDateStr = searchDate;
                    if (searchDateStr.indexOf(' ') !== -1) {
                        searchDateStr = searchDateStr.split(' ')[0];
                    }
                    if (searchDateStr.indexOf('T') !== -1) {
                        searchDateStr = searchDateStr.split('T')[0];
                    }
                    match = recordDateStr === searchDateStr;
                } else if (searchType === 'month' && searchMonth) {
                    // Use searchMonth parameter (format: YYYY-MM)
                    var monthParts = searchMonth.split('-');
                    var targetYear = parseInt(monthParts[0]);
                    var targetMonth = parseInt(monthParts[1]) - 1; // Month is 0-indexed
                    
                    // Compare using date string parts to avoid timezone issues
                    var recordDateParts = attendanceDateStr.split('-');
                    var recordYear = parseInt(recordDateParts[0]);
                    var recordMonth = parseInt(recordDateParts[1]) - 1;
                    
                    match = recordYear === targetYear && recordMonth === targetMonth;
                } else if (searchType === 'year' && searchYear) {
                    // Use searchYear parameter
                    var targetYear = parseInt(searchYear);
                    var recordDateParts = attendanceDateStr.split('-');
                    var recordYear = parseInt(recordDateParts[0]);
                    match = recordYear === targetYear;
                }

                if (match) {
                    filtered.push(rec);
                }
            });

            return filtered;
        }

        // Calculate Fingerprint Statistics
        function calculateFingerprintStats(records, totalStudentsInClass, searchType, searchMonth, searchYear) {
            var stats = {
                total_records: records.length,
                checked_in: 0,
                checked_out: 0,
                both: 0,
                unique_students: new Set(),
                total_students_in_class: totalStudentsInClass || 0,
                student_attendance: {}, // studentID -> { present_days, absent_days, total_days }
                chart_data: {
                    labels: [],
                    checked_in: [],
                    checked_out: [],
                    total_students: []
                }
            };

            // Calculate working days based on search type
            var workingDays = calculateWorkingDays(searchType, searchMonth, searchYear);
            stats.total_working_days = workingDays;

            // Group records by student
            var studentRecords = {};
            records.forEach(function(rec) {
                var studentID = rec.studentID || rec.user_id || rec.enroll_id;
                if (!studentID) return;

                if (!studentRecords[studentID]) {
                    studentRecords[studentID] = {
                        studentID: studentID,
                        user_name: rec.user_name || 'N/A',
                        class_name: rec.class_name || 'N/A',
                        fingerprint_id: rec.fingerprint_id || rec.enroll_id || studentID,
                        present_days: new Set(), // Set of unique dates with attendance
                        check_in_count: 0,
                        check_out_count: 0
                    };
                }

                var hasCheckIn = rec.check_in_time && rec.check_in_time.trim() !== '';
                var hasCheckOut = rec.check_out_time && rec.check_out_time.trim() !== '';
                var attendanceDate = rec.attendance_date;

                if (attendanceDate) {
                    // Normalize date
                    var dateStr = attendanceDate;
                    if (dateStr.indexOf(' ') !== -1) {
                        dateStr = dateStr.split(' ')[0];
                    }
                    if (dateStr.indexOf('T') !== -1) {
                        dateStr = dateStr.split('T')[0];
                    }

                    // Count as present if has check-in
                    if (hasCheckIn) {
                        studentRecords[studentID].present_days.add(dateStr);
                        studentRecords[studentID].check_in_count++;
                    }
                    if (hasCheckOut) {
                        studentRecords[studentID].check_out_count++;
                    }
                }

                stats.unique_students.add(studentID);

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
                        stats.chart_data.total_students.push(totalStudentsInClass || 0);
                    }

                    if (hasCheckIn) stats.chart_data.checked_in[dateIndex]++;
                    if (hasCheckOut) stats.chart_data.checked_out[dateIndex]++;
                }
            });

            // Calculate present/absent days for each student
            Object.keys(studentRecords).forEach(function(studentID) {
                var student = studentRecords[studentID];
                var presentDays = student.present_days.size;
                var absentDays = Math.max(0, workingDays - presentDays);

                stats.student_attendance[studentID] = {
                    studentID: studentID,
                    user_name: student.user_name,
                    class_name: student.class_name,
                    fingerprint_id: student.fingerprint_id,
                    present_days: presentDays,
                    absent_days: absentDays,
                    total_days: workingDays,
                    check_in_count: student.check_in_count,
                    check_out_count: student.check_out_count
                };
            });

            stats.unique_students_count = stats.unique_students.size;
            stats.students_with_attendance = stats.unique_students_count;
            stats.students_without_attendance = Math.max(0, stats.total_students_in_class - stats.students_with_attendance);
            stats.attendance_rate = stats.total_students_in_class > 0 ? 
                ((stats.students_with_attendance / stats.total_students_in_class) * 100).toFixed(1) : 0;
            stats.present_rate = stats.total_students_in_class > 0 ?
                (((stats.checked_in + stats.both) / stats.total_students_in_class) * 100).toFixed(1) : 0;

            return stats;
        }

        // Calculate Working Days (excluding weekends)
        function calculateWorkingDays(searchType, searchMonth, searchYear) {
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            var startDate, endDate;

            if (searchType === 'month' && searchMonth) {
                // Parse month (YYYY-MM format)
                var monthParts = searchMonth.split('-');
                var year = parseInt(monthParts[0]);
                var month = parseInt(monthParts[1]) - 1; // Month is 0-indexed
                
                startDate = new Date(year, month, 1);
                startDate.setHours(0, 0, 0, 0);
                
                // If current month, end at today; otherwise end of month
                if (year === today.getFullYear() && month === today.getMonth()) {
                    // Use today's date (already set to 00:00:00)
                    endDate = new Date(today);
                    endDate.setHours(0, 0, 0, 0); // Ensure time is 00:00:00 for comparison
                } else {
                    endDate = new Date(year, month + 1, 0); // Last day of month
                    endDate.setHours(0, 0, 0, 0);
                }
            } else if (searchType === 'year' && searchYear) {
                var year = parseInt(searchYear);
                startDate = new Date(year, 0, 1); // January 1
                startDate.setHours(0, 0, 0, 0);
                
                // If current year, end at today; otherwise end of year
                if (year === today.getFullYear()) {
                    // Use today's date (already set to 00:00:00)
                    endDate = new Date(today);
                    endDate.setHours(0, 0, 0, 0); // Ensure time is 00:00:00 for comparison
                } else {
                    endDate = new Date(year, 11, 31); // December 31
                    endDate.setHours(0, 0, 0, 0);
                }
            } else {
                // For day view, return 1 working day (if not weekend)
                if (searchType === 'day') {
                    var dayOfWeek = today.getDay();
                    return (dayOfWeek !== 0 && dayOfWeek !== 6) ? 1 : 0;
                }
                return 0;
            }

            var workingDays = 0;
            var current = new Date(startDate);
            current.setHours(0, 0, 0, 0);
            
            // Create end date for comparison (date only, no time)
            var endDateOnly = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());
            endDateOnly.setHours(0, 0, 0, 0);

            // Loop through all dates from start to end (inclusive)
            // Compare dates by comparing year, month, and day separately to avoid time issues
            while (true) {
                // Check if current date is before or equal to end date
                if (current.getFullYear() > endDateOnly.getFullYear()) break;
                if (current.getFullYear() === endDateOnly.getFullYear() && 
                    current.getMonth() > endDateOnly.getMonth()) break;
                if (current.getFullYear() === endDateOnly.getFullYear() && 
                    current.getMonth() === endDateOnly.getMonth() && 
                    current.getDate() > endDateOnly.getDate()) break;
                
                var dayOfWeek = current.getDay();
                // Skip weekends (Sunday = 0, Saturday = 6)
                if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                    workingDays++;
                }
                // Move to next day
                current.setDate(current.getDate() + 1);
            }

            return workingDays;
        }

        // Display Fingerprint Attendance Overview
        function displayFingerprintAttendanceOverview(stats, searchType, deviceFailed = false, records = [], totalStudentsInClass = 0, searchMonth = null, searchYear = null) {
            var html = '';
            
            // Check if no attendance was collected
            if (stats.total_records === 0 || stats.students_with_attendance === 0) {
                html += '<div class="alert alert-info text-center" role="alert">';
                html += '<i class="bi bi-info-circle"></i> <strong>No attendance collected</strong>';
                html += '</div>';
                $('#fingerprintAttendanceOverviewContent').html(html);
                $('#exportStudentFingerprintExcelBtn, #exportStudentFingerprintPdfBtn').hide();
                return;
            }

            // Show export buttons for all search types (day, month, year)
            $('#exportStudentFingerprintExcelBtn, #exportStudentFingerprintPdfBtn').show().prop('disabled', false).css('pointer-events', 'auto').css('opacity', '1');

            // Only show summary cards for day view (for month/year we rely on charts + exports)
            if (searchType === 'day') {
            html += '<div class="row mb-3">';
            html += '<div class="col-md-3"><div class="card bg-success"><div class="card-body text-center" style="color: #ffffff;"><h4 style="color: #ffffff;">' + (stats.checked_in + stats.both) + '</h4><p class="mb-0" style="color: #ffffff;">Checked In</p><small style="color: #ffffff;">out of ' + stats.total_students_in_class + ' students</small></div></div></div>';
            html += '<div class="col-md-3"><div class="card bg-primary"><div class="card-body text-center" style="color: #ffffff;"><h4 style="color: #ffffff;">' + (stats.checked_out + stats.both) + '</h4><p class="mb-0" style="color: #ffffff;">Checked Out</p><small style="color: #ffffff;">out of ' + stats.total_students_in_class + ' students</small></div></div></div>';
                html += '<div class="col-md-3"><div class="card bg-info"><div class="card-body text-center" style="color: #ffffff;"><h4 style="color: #ffffff;">' + stats.students_with_attendance + '</h4><p class="mb-0" style="color: #ffffff;">Present</p><small style="color: #ffffff;">out of ' + stats.total_students_in_class + ' students</small></div></div></div>';
                html += '<div class="col-md-3"><div class="card bg-warning"><div class="card-body text-center" style="color: #ffffff;"><h4 style="color: #ffffff;">' + stats.students_without_attendance + '</h4><p class="mb-0" style="color: #ffffff;">Absent</p><small style="color: #ffffff;">out of ' + stats.total_students_in_class + ' students</small></div></div></div>';
            html += '</div>';

            html += '<div class="card mb-3">';
            html += '<div class="card-header bg-primary-custom text-white"><h6 class="mb-0">Summary & Comparison</h6></div>';
            html += '<div class="card-body">';
            html += '<div class="row">';
            html += '<div class="col-md-6">';
            html += '<p><strong>Total Students in Class:</strong> ' + stats.total_students_in_class + '</p>';
                html += '<p><strong>Present:</strong> ' + stats.students_with_attendance + '</p>';
                html += '<p><strong>Absent:</strong> ' + stats.students_without_attendance + '</p>';
            html += '</div>';
            html += '<div class="col-md-6">';
            html += '<p><strong>Total Checked In:</strong> ' + (stats.checked_in + stats.both) + ' / ' + stats.total_students_in_class + '</p>';
            html += '<p><strong>Total Checked Out:</strong> ' + (stats.checked_out + stats.both) + ' / ' + stats.total_students_in_class + '</p>';
            html += '<p><strong>Attendance Rate:</strong> <span class="badge bg-success">' + stats.attendance_rate + '%</span></p>';
            html += '<p><strong>Present Rate (Checked In):</strong> <span class="badge bg-info">' + stats.present_rate + '%</span></p>';
            html += '</div>';
            html += '</div>';
            html += '</div></div>';
            }

            $('#fingerprintAttendanceOverviewContent').html(html);
            
            // Display student attendance table for all views (day, month, year)
            // For day view, show detailed attendance records table
            if (searchType === 'day') {
                // Show detailed attendance records table for day view
                $('#fingerprintAttendanceOverviewContent').append(displayDayViewAttendanceTable(records, totalStudentsInClass));
            } else if (searchType === 'month' || searchType === 'year') {
                // Show summary table for month/year view
                $('#fingerprintAttendanceOverviewContent').append(displayStudentAttendanceTable(stats, totalStudentsInClass, searchMonth, searchYear));
            }
        }

        // Display Day View Attendance Table (Detailed Records)
        function displayDayViewAttendanceTable(records, totalStudentsInClass) {
            var html = '<div class="card mt-4">';
            html += '<div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center">';
            html += '<h6 class="mb-0"><i class="bi bi-table"></i> Detailed Attendance Records</h6>';
            html += '</div>';
            html += '<div class="card-body">';
            html += '<div class="table-responsive">';
            html += '<table class="table table-striped table-hover" id="dayViewAttendanceTable">';
            html += '<thead class="bg-light">';
            html += '<tr>';
            html += '<th>#</th>';
            html += '<th>Student Name</th>';
            html += '<th>Class</th>';
            html += '<th>Fingerprint ID</th>';
            html += '<th>Check In Time</th>';
            html += '<th>Check Out Time</th>';
            html += '<th>Verify Mode</th>';
            html += '<th>Device IP</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';

            // Group records by student to show all check-in/check-out times
            var studentRecords = {};
            records.forEach(function(rec) {
                var studentId = rec.studentID || rec.user_id || rec.enroll_id || '';
                if (!studentId) return;

                if (!studentRecords[studentId]) {
                    studentRecords[studentId] = {
                        studentName: rec.user_name || 'N/A',
                        className: rec.class_name || 'N/A',
                        fingerprintId: rec.fingerprint_id || rec.enroll_id || studentId,
                        records: []
                    };
                }

                if (rec.check_in_time || rec.check_out_time) {
                    // Format times to HH:mm:ss only
                    var checkInTime = rec.check_in_time || '-';
                    var checkOutTime = rec.check_out_time || '-';
                    
                    // Extract time part if datetime format
                    if (checkInTime !== '-' && checkInTime.indexOf(' ') !== -1) {
                        checkInTime = checkInTime.split(' ')[1] || checkInTime;
                    }
                    if (checkOutTime !== '-' && checkOutTime.indexOf(' ') !== -1) {
                        checkOutTime = checkOutTime.split(' ')[1] || checkOutTime;
                    }
                    
                    studentRecords[studentId].records.push({
                        checkInTime: checkInTime,
                        checkOutTime: checkOutTime,
                        verifyMode: rec.verify_mode || 'Fingerprint',
                        deviceIp: rec.device_ip || 'N/A'
                    });
                }
            });

            var rowIndex = 1;
            Object.keys(studentRecords).forEach(function(studentId) {
                var student = studentRecords[studentId];
                student.records.forEach(function(rec, idx) {
                    html += '<tr>';
                    html += '<td>' + rowIndex + '</td>';
                    if (idx === 0) {
                        html += '<td rowspan="' + student.records.length + '"><strong>' + student.studentName + '</strong></td>';
                        html += '<td rowspan="' + student.records.length + '">' + student.className + '</td>';
                        html += '<td rowspan="' + student.records.length + '">' + student.fingerprintId + '</td>';
                    }
                    html += '<td>' + (rec.checkInTime !== '-' ? '<span class="badge bg-success">' + rec.checkInTime + '</span>' : '<span class="text-muted">-</span>') + '</td>';
                    html += '<td>' + (rec.checkOutTime !== '-' ? '<span class="badge bg-primary">' + rec.checkOutTime + '</span>' : '<span class="text-muted">-</span>') + '</td>';
                    html += '<td>' + rec.verifyMode + '</td>';
                    html += '<td>' + rec.deviceIp + '</td>';
                    html += '</tr>';
                    rowIndex++;
                });
            });

            html += '</tbody>';
            html += '</table>';
            html += '</div>';
            html += '</div>';
            html += '</div>';

            // Initialize DataTable after appending to DOM
            setTimeout(function() {
                if ($.fn.DataTable && $('#dayViewAttendanceTable').length) {
                    // Destroy existing DataTable if it exists
                    if ($.fn.DataTable.isDataTable('#dayViewAttendanceTable')) {
                        $('#dayViewAttendanceTable').DataTable().destroy();
                    }
                    
                    // Initialize new DataTable
                    $('#dayViewAttendanceTable').DataTable({
                        pageLength: 25,
                        order: [[1, 'asc']], // Sort by Student Name
                        language: {
                            search: "Search:",
                            lengthMenu: "Show _MENU_ entries",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            infoEmpty: "No entries found",
                            infoFiltered: "(filtered from _MAX_ total entries)",
                            zeroRecords: "No matching records found"
                        },
                        responsive: true,
                        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
                    });
                }
            }, 100);

            return html;
        }

        // Display Student Attendance Table
        function displayStudentAttendanceTable(stats, totalStudentsInClass, searchMonth, searchYear) {
            // Get all students from blade (passed from controller)
            var allStudents = currentSubclassStudents; // Use the stored students
            var workingDays = stats.total_working_days || 0;

            // Build complete student list with attendance data
            var completeStudentList = [];
            allStudents.forEach(function(student) {
                var studentId = student.studentID || student.fingerprint_id || '';
                var fullName = (student.first_name || '') + ' ' + (student.middle_name ? student.middle_name + ' ' : '') + (student.last_name || '');
                fullName = fullName.trim() || 'N/A';
                
                // Get attendance data from stats
                var attendanceData = stats.student_attendance[studentId] || {
                    studentID: studentId,
                    user_name: fullName,
                    class_name: student.subclass_name || 'N/A',
                    fingerprint_id: student.fingerprint_id || studentId,
                    present_days: 0,
                    absent_days: workingDays,
                    total_days: workingDays,
                    check_in_count: 0,
                    check_out_count: 0
                };
                
                completeStudentList.push(attendanceData);
            });

            // Sort by name
            completeStudentList.sort(function(a, b) {
                return a.user_name.localeCompare(b.user_name);
            });

            var html = '<div class="card mt-4">';
            html += '<div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center">';
            html += '<h6 class="mb-0"><i class="bi bi-table"></i> Student Attendance Records</h6>';
            html += '</div>';
            html += '<div class="card-body">';
            html += '<div class="table-responsive">';
            html += '<table class="table table-striped table-hover" id="studentAttendanceOverviewTable">';
            html += '<thead class="bg-light">';
            html += '<tr>';
            html += '<th>#</th>';
            html += '<th>Student Name</th>';
            html += '<th>Class</th>';
            html += '<th>Fingerprint ID</th>';
            html += '<th>Present Days</th>';
            html += '<th>Absent Days</th>';
            html += '<th>Total Days</th>';
            html += '<th>Present %</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';

            completeStudentList.forEach(function(student, index) {
                var presentDays = student.present_days || 0;
                var absentDays = student.absent_days || 0;
                var totalDays = student.total_days || workingDays;
                var presentPercentage = totalDays > 0 ? ((presentDays / totalDays) * 100).toFixed(1) : 0;

                html += '<tr>';
                html += '<td>' + (index + 1) + '</td>';
                html += '<td><strong>' + (student.user_name || 'N/A') + '</strong></td>';
                html += '<td>' + (student.class_name || 'N/A') + '</td>';
                html += '<td>' + (student.fingerprint_id || 'N/A') + '</td>';
                html += '<td><span class="badge bg-success">' + presentDays + '</span></td>';
                html += '<td><span class="badge bg-danger">' + absentDays + '</span></td>';
                html += '<td>' + totalDays + '</td>';
                html += '<td><span class="badge bg-info">' + presentPercentage + '%</span></td>';
                html += '</tr>';
            });

            html += '</tbody>';
            html += '</table>';
            html += '</div>';
            html += '</div>';
            html += '</div>';

            // Initialize DataTable after appending to DOM
            setTimeout(function() {
                if ($.fn.DataTable && $('#studentAttendanceOverviewTable').length) {
                    // Destroy existing DataTable if it exists
                    if ($.fn.DataTable.isDataTable('#studentAttendanceOverviewTable')) {
                        $('#studentAttendanceOverviewTable').DataTable().destroy();
                    }
                    
                    // Initialize new DataTable
                    $('#studentAttendanceOverviewTable').DataTable({
                        pageLength: 25,
                        order: [[1, 'asc']], // Sort by Student Name
                        language: {
                            search: "Search:",
                            lengthMenu: "Show _MENU_ entries",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            infoEmpty: "No entries found",
                            infoFiltered: "(filtered from _MAX_ total entries)",
                            zeroRecords: "No matching records found"
                        },
                        responsive: true,
                        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
                    });
                }
            }, 100);

            return html;
        }

        // Generate Fingerprint Attendance Charts
        var fingerprintAttendanceChart = null;
        var fingerprintStatusChart = null;

        function generateFingerprintAttendanceCharts(records, stats, searchType, totalStudentsInClass, searchMonth = null, searchYear = null) {
            // Destroy existing charts if they exist
            if (fingerprintAttendanceChart) {
                fingerprintAttendanceChart.destroy();
            }
            if (fingerprintStatusChart) {
                fingerprintStatusChart.destroy();
            }

            // Check if Chart.js is available
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js is not loaded. Please include Chart.js library.');
                return;
            }

            var ctx1 = document.getElementById('fingerprintAttendanceChart');
            var ctx2 = document.getElementById('fingerprintStatusChart');

            if (!ctx1 || !ctx2) return;

            // For month/year show Check In vs Check Out (Pie) and Present vs Absent % (Bar)
            if (searchType === 'month' || searchType === 'year') {
                // Calculate Check In vs Check Out counts
                var checkInCount = stats.checked_in + stats.both;
                var checkOutCount = stats.checked_out + stats.both;
                var totalCheckEvents = checkInCount + checkOutCount;
                var checkInPercent = totalCheckEvents > 0 ? ((checkInCount / totalCheckEvents) * 100).toFixed(1) : 0;
                var checkOutPercent = totalCheckEvents > 0 ? ((checkOutCount / totalCheckEvents) * 100).toFixed(1) : 0;

                // Calculate Present vs Absent percentages based on working days
                var totalWorkingDays = stats.total_working_days || 0;
                var totalPossiblePresentDays = totalStudentsInClass * totalWorkingDays;
                var totalActualPresentDays = 0;
                
                // Sum up all present days from all students
                Object.keys(stats.student_attendance || {}).forEach(function(studentID) {
                    totalActualPresentDays += (stats.student_attendance[studentID].present_days || 0);
                });
                
                var totalAbsentDays = totalPossiblePresentDays - totalActualPresentDays;
                var presentPercentage = totalPossiblePresentDays > 0 ? ((totalActualPresentDays / totalPossiblePresentDays) * 100).toFixed(1) : 0;
                var absentPercentage = totalPossiblePresentDays > 0 ? ((totalAbsentDays / totalPossiblePresentDays) * 100).toFixed(1) : 0;

                // Show both charts
                if (ctx1 && ctx1.parentElement) {
                    ctx1.parentElement.parentElement.style.display = 'block';
                }
                if (ctx2 && ctx2.parentElement) {
                    ctx2.parentElement.parentElement.style.display = 'block';
                }

                // Pie Chart - Check In vs Check Out Status
                fingerprintStatusChart = new Chart(ctx2, {
                    type: 'pie',
                    data: {
                        labels: ['Check In (' + checkInPercent + '%)', 'Check Out (' + checkOutPercent + '%)'],
                        datasets: [{
                            data: [checkInCount, checkOutCount],
                            backgroundColor: [
                                '#28a745', // Green for Check In
                                '#007bff'  // Blue for Check Out
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Check In vs Check Out Status'
                            },
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        var label = context.label || '';
                                        var value = context.parsed || 0;
                                        var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return label + ': ' + value + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });

                // Bar Chart - Present vs Absent as Percentage
                fingerprintAttendanceChart = new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: ['Present (' + presentPercentage + '%)', 'Absent (' + absentPercentage + '%)'],
                        datasets: [{
                            label: 'Percentage',
                            data: [parseFloat(presentPercentage), parseFloat(absentPercentage)],
                            backgroundColor: ['#28a745', '#dc3545']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'Attendance Percentage for Selected Period'
                            },
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.y.toFixed(1) + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                // Day view - keep original charts
            fingerprintAttendanceChart = new Chart(ctx1, {
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
                        label: 'Total Students in Class',
                        data: stats.chart_data.total_students || [],
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
                            max: Math.max(totalStudentsInClass || 0, ...(stats.chart_data.checked_in || []), ...(stats.chart_data.checked_out || []))
                        }
                    }
                }
            });

                // Status Distribution Chart (Pie Chart)
            fingerprintStatusChart = new Chart(ctx2, {
                type: 'pie',
                data: {
                        labels: ['Present', 'Absent'],
                    datasets: [{
                        data: [stats.students_with_attendance, stats.students_without_attendance],
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

        // Export Student Fingerprint Attendance to Excel (JavaScript)
        function exportStudentFingerprintToExcel() {
            if (typeof XLSX === 'undefined') {
                Swal.fire('Error', 'Excel export library not loaded', 'error');
                return;
            }

            if (!currentFingerprintFilteredRecords || currentFingerprintFilteredRecords.length === 0) {
                Swal.fire('Error', 'No data to export. Please generate overview first.', 'error');
                return;
            }

            // Get school name and build title (caps)
            var schoolName = '{{ $school_details->school_name ?? "School" }}';
            var reportTitle = '';
            if (currentFingerprintSearchType === 'month' && currentFingerprintSearchMonth) {
                var monthParts = currentFingerprintSearchMonth.split('-');
                var monthNames = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
                reportTitle = 'STUDENT ATTENDANCE IN ' + monthNames[parseInt(monthParts[1]) - 1] + ' ' + monthParts[0];
            } else if (currentFingerprintSearchType === 'year' && currentFingerprintSearchYear) {
                reportTitle = 'STUDENT ATTENDANCE IN ' + currentFingerprintSearchYear;
            } else {
                reportTitle = 'STUDENT ATTENDANCE';
            }

            // Calculate working days
            var startDate, endDate;
            if (currentFingerprintSearchMonth) {
                var monthParts = currentFingerprintSearchMonth.split('-');
                startDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]) - 1, 1);
                endDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]), 0);
                if (endDate > new Date()) endDate = new Date();
            } else if (currentFingerprintSearchYear) {
                startDate = new Date(parseInt(currentFingerprintSearchYear), 0, 1);
                endDate = new Date(parseInt(currentFingerprintSearchYear), 11, 31);
                if (endDate > new Date()) endDate = new Date();
            } else {
                // Day view - single working day if not weekend
                var labels = (currentFingerprintFilteredRecords[0] && currentFingerprintFilteredRecords[0].attendance_date)
                    ? [currentFingerprintFilteredRecords[0].attendance_date]
                    : [];
                if (labels.length > 0) {
                    startDate = endDate = new Date(labels[0]);
                } else {
                    Swal.fire('Error', 'No date information available for export.', 'error');
                    return;
                }
            }

            var workingDays = 0;
            var current = new Date(startDate);
            while (current <= endDate) {
                if (current.getDay() !== 0 && current.getDay() !== 6) {
                    workingDays++;
                }
                current.setDate(current.getDate() + 1);
            }

            // Group records by student
            var studentMap = {};
            currentFingerprintFilteredRecords.forEach(function(rec) {
                var studentId = (rec.student_info && rec.student_info.studentID) || (rec.user && rec.user.enroll_id) || '';
                if (!studentId) return;

                if (!studentMap[studentId]) {
                    var fullName = 'N/A';
                    if (rec.student_info) {
                        fullName = (rec.student_info.first_name || '') + ' ' +
                                   (rec.student_info.middle_name ? rec.student_info.middle_name + ' ' : '') +
                                   (rec.student_info.last_name || '');
                        fullName = fullName.trim() || 'N/A';
                    } else if (rec.user && rec.user.name) {
                        fullName = rec.user.name;
                    }

                    studentMap[studentId] = {
                        name: fullName,
                        presentDates: new Set()
                    };
                }

                // Only count if student has check_in_time (present)
                if (rec.check_in_time && rec.check_in_time.trim() !== '') {
                    // Normalize attendance_date to YYYY-MM-DD format (remove time if present)
                    var attendanceDate = rec.attendance_date;
                    if (attendanceDate) {
                        // If date includes time, extract only date part
                        if (attendanceDate.indexOf(' ') !== -1) {
                            attendanceDate = attendanceDate.split(' ')[0];
                        }
                        // If date includes T (ISO format), extract only date part
                        if (attendanceDate.indexOf('T') !== -1) {
                            attendanceDate = attendanceDate.split('T')[0];
                        }
                        studentMap[studentId].presentDates.add(attendanceDate);
                    }
                }
            });

            // Add all students from subclass (count 0 if no attendance)
            if (currentSubclassStudents && currentSubclassStudents.length > 0) {
                currentSubclassStudents.forEach(function(stu) {
                    var studentId = stu.studentID || stu.fingerprint_id || '';
                    if (!studentId) return;

                    if (!studentMap[studentId]) {
                        var fullName = (stu.first_name || '') + ' ' +
                                       (stu.middle_name ? stu.middle_name + ' ' : '') +
                                       (stu.last_name || '');
                        fullName = fullName.trim() || 'N/A';

                        studentMap[studentId] = {
                            name: fullName,
                            presentDates: new Set()
                        };
                    }
                });
            }

            var studentList = Object.values(studentMap);
            studentList.sort(function(a, b) {
                return a.name.localeCompare(b.name);
            });

            // Create workbook
            var wb = XLSX.utils.book_new();
            var wsData = [];

            // Header rows
            wsData.push([schoolName]);
            wsData.push([reportTitle]);
            wsData.push([]);
            wsData.push(['Student Name', 'Days Present', 'Days Absent', 'Total Days']);

            // Student data
            studentList.forEach(function(student) {
                var daysPresent = student.presentDates.size;
                var daysAbsent = Math.max(0, workingDays - daysPresent);
                wsData.push([student.name, daysPresent, daysAbsent, workingDays]);
            });

            var ws = XLSX.utils.aoa_to_sheet(wsData);
            if (!ws['!merges']) ws['!merges'] = [];
            ws['!merges'].push({s: {r: 0, c: 0}, e: {r: 0, c: 3}});
            ws['!merges'].push({s: {r: 1, c: 0}, e: {r: 1, c: 3}});

            XLSX.utils.book_append_sheet(wb, ws, 'Student Attendance');
            XLSX.writeFile(wb, 'Student_Fingerprint_Attendance_' + (currentFingerprintSearchMonth || currentFingerprintSearchYear || 'Report') + '_' + new Date().toISOString().split('T')[0] + '.xlsx');
        }

        // Export Student Fingerprint Attendance to PDF (JavaScript)
        function exportStudentFingerprintToPdf() {
            if (typeof window.jspdf === 'undefined') {
                Swal.fire('Error', 'PDF export library not loaded', 'error');
                return;
            }

            if (!currentFingerprintFilteredRecords || currentFingerprintFilteredRecords.length === 0) {
                Swal.fire('Error', 'No data to export. Please generate overview first.', 'error');
                return;
            }

            var { jsPDF } = window.jspdf;
            var doc = new jsPDF('landscape');

            // Get subclass name from first record or students list
            var subclassName = 'N/A';
            if (currentFingerprintFilteredRecords.length > 0) {
                subclassName = currentFingerprintFilteredRecords[0].class_name || 'N/A';
            } else if (currentSubclassStudents && currentSubclassStudents.length > 0) {
                subclassName = currentSubclassStudents[0].subclass_name || 'N/A';
            }

            // Get school name and build title (caps)
            var schoolName = '{{ $school_details->school_name ?? "School" }}';
            var reportTitle = '';
            if (currentFingerprintSearchType === 'day' && currentFingerprintSearchDate) {
                var dateObj = new Date(currentFingerprintSearchDate);
                var monthNames = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
                var day = dateObj.getDate();
                var month = monthNames[dateObj.getMonth()];
                var year = dateObj.getFullYear();
                reportTitle = 'STUDENT ATTENDANCE OF ' + subclassName.toUpperCase() + ' ON ' + day + ' ' + month + ' ' + year;
            } else if (currentFingerprintSearchType === 'month' && currentFingerprintSearchMonth) {
                var monthParts = currentFingerprintSearchMonth.split('-');
                var monthNames = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
                reportTitle = 'STUDENT ATTENDANCE OF ' + subclassName.toUpperCase() + ' IN ' + monthNames[parseInt(monthParts[1]) - 1] + ' ' + monthParts[0];
            } else if (currentFingerprintSearchType === 'year' && currentFingerprintSearchYear) {
                reportTitle = 'STUDENT ATTENDANCE OF ' + subclassName.toUpperCase() + ' IN ' + currentFingerprintSearchYear;
            } else {
                reportTitle = 'STUDENT ATTENDANCE OF ' + subclassName.toUpperCase();
            }

            // Calculate working days and prepare data
            var startDate, endDate;
            var workingDays = 0;
            var totalStudents = currentFingerprintTotalStudents || 0;
            var presentCount = 0;
            var absentCount = 0;
            var checkInCount = 0;
            var checkOutCount = 0;
            var detailedRecords = []; // For day view detailed records

            if (currentFingerprintSearchType === 'day' && currentFingerprintSearchDate) {
                startDate = endDate = new Date(currentFingerprintSearchDate);
                var dayOfWeek = startDate.getDay();
                workingDays = (dayOfWeek !== 0 && dayOfWeek !== 6) ? 1 : 0;
            } else if (currentFingerprintSearchMonth) {
                var monthParts = currentFingerprintSearchMonth.split('-');
                startDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]) - 1, 1);
                endDate = new Date(parseInt(monthParts[0]), parseInt(monthParts[1]), 0);
                if (endDate > new Date()) endDate = new Date();
            } else if (currentFingerprintSearchYear) {
                startDate = new Date(parseInt(currentFingerprintSearchYear), 0, 1);
                endDate = new Date(parseInt(currentFingerprintSearchYear), 11, 31);
                if (endDate > new Date()) endDate = new Date();
                } else {
                    Swal.fire('Error', 'No date information available for export.', 'error');
                    return;
            }

            // Calculate working days for month/year
            if (currentFingerprintSearchType !== 'day') {
                startDate.setHours(0, 0, 0, 0);
                endDate.setHours(0, 0, 0, 0);
                
                var current = new Date(startDate);
                var endDateOnly = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());
                endDateOnly.setHours(0, 0, 0, 0);
                
                // Loop through all dates from start to end (inclusive)
                // Compare dates by comparing year, month, and day separately to avoid time issues
                while (true) {
                    // Check if current date is before or equal to end date
                    if (current.getFullYear() > endDateOnly.getFullYear()) break;
                    if (current.getFullYear() === endDateOnly.getFullYear() && 
                        current.getMonth() > endDateOnly.getMonth()) break;
                    if (current.getFullYear() === endDateOnly.getFullYear() && 
                        current.getMonth() === endDateOnly.getMonth() && 
                        current.getDate() > endDateOnly.getDate()) break;
                    
                    var dayOfWeek = current.getDay();
                    // Skip weekends (Sunday = 0, Saturday = 6)
                    if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                        workingDays++;
                    }
                    // Move to next day
                    current.setDate(current.getDate() + 1);
                }
            }

            // Group records by student - use same format as Excel (rec.studentID, rec.user_name, etc.)
            var studentMap = {};
            currentFingerprintFilteredRecords.forEach(function(rec) {
                var studentId = rec.studentID || rec.user_id || rec.enroll_id || '';
                var studentName = rec.user_name || 'N/A';
                var className = rec.class_name || 'N/A';
                var fingerprintId = rec.fingerprint_id || rec.enroll_id || studentId;
                
                if (!studentId) return;

                // For day view, collect detailed records
                if (currentFingerprintSearchType === 'day') {
                    if (rec.check_in_time || rec.check_out_time) {
                        // Format times to HH:mm:ss only
                        var checkInTime = rec.check_in_time || '-';
                        var checkOutTime = rec.check_out_time || '-';
                        
                        // Extract time part if datetime format
                        if (checkInTime !== '-') {
                            if (checkInTime.indexOf(' ') !== -1) {
                                checkInTime = checkInTime.split(' ')[1] || checkInTime;
                            } else if (checkInTime.indexOf('T') !== -1) {
                                var timePart = checkInTime.split('T')[1];
                                if (timePart) {
                                    checkInTime = timePart.split('.')[0]; // Remove milliseconds if present
                                }
                            }
                        }
                        if (checkOutTime !== '-') {
                            if (checkOutTime.indexOf(' ') !== -1) {
                                checkOutTime = checkOutTime.split(' ')[1] || checkOutTime;
                            } else if (checkOutTime.indexOf('T') !== -1) {
                                var timePart = checkOutTime.split('T')[1];
                                if (timePart) {
                                    checkOutTime = timePart.split('.')[0]; // Remove milliseconds if present
                                }
                            }
                        }
                        
                        detailedRecords.push({
                            studentName: studentName,
                            className: className,
                            fingerprintId: fingerprintId,
                            checkInTime: checkInTime,
                            checkOutTime: checkOutTime,
                            verifyMode: rec.verify_mode || 'Fingerprint',
                            deviceIp: rec.device_ip || 'N/A'
                        });
                    }
                    if (rec.check_in_time && rec.check_in_time.trim() !== '') {
                        checkInCount++;
                    }
                    if (rec.check_out_time && rec.check_out_time.trim() !== '') {
                        checkOutCount++;
                    }
                }

                if (!studentMap[studentId]) {
                    studentMap[studentId] = {
                        name: studentName,
                        className: className,
                        fingerprintId: fingerprintId,
                        presentDates: new Set(),
                        hasCheckIn: false,
                        hasCheckOut: false
                    };
                }

                // Only count if student has check_in_time (present)
                if (rec.check_in_time && rec.check_in_time.trim() !== '') {
                    studentMap[studentId].hasCheckIn = true;
                    // Normalize attendance_date to YYYY-MM-DD format (remove time if present)
                    var attendanceDate = rec.attendance_date;
                    if (attendanceDate) {
                        // If date includes time, extract only date part
                        if (attendanceDate.indexOf(' ') !== -1) {
                            attendanceDate = attendanceDate.split(' ')[0];
                        }
                        // If date includes T (ISO format), extract only date part
                        if (attendanceDate.indexOf('T') !== -1) {
                            attendanceDate = attendanceDate.split('T')[0];
                        }
                        studentMap[studentId].presentDates.add(attendanceDate);
                    }
                }
                
                if (rec.check_out_time && rec.check_out_time.trim() !== '') {
                    studentMap[studentId].hasCheckOut = true;
                }
            });

            // Add all students from subclass (count 0 if no attendance)
            if (currentSubclassStudents && currentSubclassStudents.length > 0) {
                currentSubclassStudents.forEach(function(stu) {
                    var studentId = stu.studentID || stu.fingerprint_id || '';
                    if (!studentId) return;

                    if (!studentMap[studentId]) {
                        var fullName = (stu.first_name || '') + ' ' +
                                       (stu.middle_name ? stu.middle_name + ' ' : '') +
                                       (stu.last_name || '');
                        fullName = fullName.trim() || 'N/A';

                        studentMap[studentId] = {
                            name: fullName,
                            className: stu.subclass_name || subclassName,
                            fingerprintId: stu.fingerprint_id || studentId,
                            presentDates: new Set(),
                            hasCheckIn: false,
                            hasCheckOut: false
                        };
                    }
                });
            }

            // Calculate present/absent counts
            Object.keys(studentMap).forEach(function(studentId) {
                var student = studentMap[studentId];
                if (student.presentDates.size > 0 || student.hasCheckIn) {
                    presentCount++;
                } else {
                    absentCount++;
                }
            });

            var studentList = Object.values(studentMap);
            studentList.sort(function(a, b) {
                return a.name.localeCompare(b.name);
            });

            // Add header with logo and centered titles
            var schoolLogoUrl = '{{ $school_details->school_logo ? asset($school_details->school_logo) : "" }}';

            function drawHeaderAndTable(logoImg) {
                var pageWidth = doc.internal.pageSize.getWidth();
                var centerX = pageWidth / 2;

                // Logo on the left if available
                if (logoImg) {
                    try {
                        doc.addImage(logoImg, 'PNG', 14, 10, 24, 24);
                    } catch (e) {
                        console.warn('Failed to add logo to PDF:', e);
                    }
                }

                // School name and report title centered
                doc.setFontSize(16);
                doc.text(schoolName.toUpperCase(), centerX, 18, { align: 'center' });
                doc.setFontSize(12);
                doc.text(reportTitle, centerX, 26, { align: 'center' });

                var startY = 36;

                // Overview summary
                startY += 5;
                doc.setFontSize(11);
                doc.setFont(undefined, 'bold');
                doc.text('OVERVIEW', 14, startY);
                startY += 6;
                doc.setFont(undefined, 'normal');
                doc.setFontSize(10);
                doc.text('Total Students: ' + totalStudents, 14, startY);
                startY += 5;
                doc.text('Present: ' + presentCount, 14, startY);
                startY += 5;
                doc.text('Absent: ' + absentCount, 14, startY);
                if (currentFingerprintSearchType === 'day') {
                    startY += 5;
                    doc.text('Check In Count: ' + checkInCount, 14, startY);
                    startY += 5;
                    doc.text('Check Out Count: ' + checkOutCount, 14, startY);
                }
                startY += 8;

                // For day view, show detailed records
                if (currentFingerprintSearchType === 'day') {
                    // Detailed attendance records table
                    doc.setFont(undefined, 'bold');
                    doc.setFontSize(11);
                    doc.text('DETAILED ATTENDANCE RECORDS', 14, startY);
                    startY += 6;

                    var detailedTableData = [];
                    detailedRecords.forEach(function(rec) {
                        detailedTableData.push([
                            rec.studentName,
                            rec.className,
                            rec.fingerprintId,
                            rec.checkInTime,
                            rec.checkOutTime,
                            rec.verifyMode,
                            rec.deviceIp
                        ]);
                    });

                    doc.autoTable({
                        startY: startY,
                        head: [['Student Name', 'Class', 'Fingerprint ID', 'Check In Time', 'Check Out Time', 'Verify Mode', 'Device IP']],
                        body: detailedTableData,
                        theme: 'striped',
                        headStyles: { fillColor: [148, 0, 0] },
                        styles: { fontSize: 8 },
                        didDrawPage: function (data) {
                            // Footer on each page
                            var pageHeight = doc.internal.pageSize.getHeight();
                            doc.setFontSize(9);
                            doc.text('Powered by: EmCa Technologies LTD', centerX, pageHeight - 8, { align: 'center' });
                        }
                    });

                    // All students table
                    var lastY = doc.lastAutoTable.finalY + 10;
                    doc.setFont(undefined, 'bold');
                    doc.setFontSize(11);
                    doc.text('ALL STUDENTS', 14, lastY);
                    lastY += 6;

                    var allStudentsTableData = [];
                    studentList.forEach(function(student) {
                        var status = student.hasCheckIn ? 'Present' : 'Absent';
                        allStudentsTableData.push([
                            student.name,
                            student.className,
                            student.fingerprintId,
                            status
                        ]);
                    });

                    doc.autoTable({
                        startY: lastY,
                        head: [['Student Name', 'Class', 'Fingerprint ID', 'Status']],
                        body: allStudentsTableData,
                        theme: 'striped',
                        headStyles: { fillColor: [148, 0, 0] },
                        styles: { fontSize: 8 },
                        didDrawPage: function (data) {
                            // Footer on each page
                            var pageHeight = doc.internal.pageSize.getHeight();
                            doc.setFontSize(9);
                            doc.text('Powered by: EmCa Technologies LTD', centerX, pageHeight - 8, { align: 'center' });
                        }
                    });
                } else {
                    // For month/year view, show summary table
                var tableData = [];
                studentList.forEach(function(student) {
                    var daysPresent = student.presentDates.size;
                    var daysAbsent = Math.max(0, workingDays - daysPresent);
                        var presentPercentage = workingDays > 0 ? ((daysPresent / workingDays) * 100).toFixed(1) : 0;
                        tableData.push([
                            student.name,
                            student.className || subclassName,
                            student.fingerprintId || 'N/A',
                            daysPresent,
                            daysAbsent,
                            workingDays,
                            presentPercentage + '%'
                        ]);
                    });

                doc.autoTable({
                        startY: startY,
                        head: [['Student Name', 'Class', 'Fingerprint ID', 'Present Days', 'Absent Days', 'Total Days', 'Present %']],
                    body: tableData,
                    theme: 'striped',
                    headStyles: { fillColor: [148, 0, 0] },
                        styles: { fontSize: 8 },
                    didDrawPage: function (data) {
                        // Footer on each page
                        var pageHeight = doc.internal.pageSize.getHeight();
                        doc.setFontSize(9);
                        doc.text('Powered by: EmCa Technologies LTD', centerX, pageHeight - 8, { align: 'center' });
                    }
                });
                }

                // Save PDF
                var fileName = 'Student_Fingerprint_Attendance_';
                if (currentFingerprintSearchType === 'day' && currentFingerprintSearchDate) {
                    fileName += currentFingerprintSearchDate.replace(/-/g, '_');
                } else if (currentFingerprintSearchMonth) {
                    fileName += currentFingerprintSearchMonth.replace(/-/g, '_');
                } else if (currentFingerprintSearchYear) {
                    fileName += currentFingerprintSearchYear;
                } else {
                    fileName += 'Report';
                }
                fileName += '_' + new Date().toISOString().split('T')[0] + '.pdf';
                doc.save(fileName);
            }

            if (schoolLogoUrl) {
                var img = new Image();
                img.crossOrigin = 'Anonymous';
                img.onload = function() {
                    drawHeaderAndTable(img);
                };
                img.onerror = function() {
                    console.warn('Failed to load school logo image for PDF header.');
                    drawHeaderAndTable(null);
                };
                img.src = schoolLogoUrl;
            } else {
                drawHeaderAndTable(null);
            }
        }

        // Bind export buttons - use event delegation to ensure they work
        $(document).off('click', '#exportStudentFingerprintExcelBtn').on('click', '#exportStudentFingerprintExcelBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $btn = $(this);
            if ($btn.prop('disabled') || $btn.css('pointer-events') === 'none') {
                console.log('Button is disabled or pointer-events is none');
                return false;
            }
            console.log('Excel export clicked');
            exportStudentFingerprintToExcel();
        });

        $(document).off('click', '#exportStudentFingerprintPdfBtn').on('click', '#exportStudentFingerprintPdfBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $btn = $(this);
            if ($btn.prop('disabled') || $btn.css('pointer-events') === 'none') {
                console.log('Button is disabled or pointer-events is none');
                return false;
            }
            console.log('PDF export clicked');
            exportStudentFingerprintToPdf();
        });

        function loadFingerprintAttendanceTeacher(page = 1) {
            const container = $('#fingerprintAttendanceContentTeacher');
            const dateFilter = $('#fingerprintAttendanceDateFilter').val();

            container.html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 mb-0">Loading attendance from database...</p>
                </div>
            `);

            // Load from database table (not device)
            $.ajax({
                url: '{{ route("zkteco.attendance.from-db") }}',
                type: 'GET',
                data: { 
                    page: page,
                    subclassID: subclassID,
                    date: dateFilter || null
                },
                dataType: 'json',
                success: function(data) {
                    if (!data.success) {
                        container.html(`
                            <div class="alert alert-danger mb-0">
                                <i class="bi bi-exclamation-triangle"></i> ${data.message || 'Failed to load attendance from device.'}
                            </div>
                        `);
                        return;
                    }

                    let records = data.data || [];
                    const pagination = data.pagination || null;
                    const isFromDB = data.source === 'database'; // Check if data came from DB fallback

                    // Filter by date if selected
                    const dateFilter = $('#fingerprintAttendanceDateFilter').val();
                    if (dateFilter) {
                        records = records.filter(rec => rec.attendance_date === dateFilter);
                    }

                    if (records.length === 0) {
                        const isFromDB = data.source === 'database';
                        const message = isFromDB 
                            ? 'No attendance records found in local database' + (dateFilter ? ' for the selected date' : '') + '. The device is currently unavailable.'
                            : 'No attendance records found' + (dateFilter ? ' for the selected date' : ' from the biometric device') + '.';
                        
                        container.html(`
                            <div class="alert ${isFromDB ? 'alert-warning' : 'alert-info'} mb-0">
                                <i class="bi bi-${isFromDB ? 'exclamation-triangle' : 'info-circle'}"></i> ${message}
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
                            <table class="table table-striped table-hover table-sm" id="fingerprintAttendanceTableTeacher">
                                <thead class="bg-primary-custom text-white">
                                    <tr>
                                        <th>#</th>
                                        <th>Student Name</th>
                                        <th>Class</th>
                                        <th>Fingerprint ID</th>
                                        <th>Attendance Date</th>
                                        <th>Check In Time</th>
                                        <th>Check Out Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    $.each(records, function(index, rec) {
                        // Use new format from device (direct)
                        const fullName = rec.user_name || 'N/A';
                        const subclassName = rec.class_name || 'N/A';
                        const fingerprintId = rec.fingerprint_id || rec.enroll_id || rec.user_id || 'N/A';
                        const attendanceDate = rec.attendance_date || '';
                        
                        // Format times to HH:mm:ss only
                        const checkInTime = formatTimeOnly(rec.check_in_time || '');
                        const checkOutTime = formatTimeOnly(rec.check_out_time || '');

                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td><strong>${fullName}</strong></td>
                                <td>${subclassName}</td>
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
                                <button class="btn btn-sm btn-outline-secondary me-1" onclick="loadFingerprintAttendanceTeacher(${pagination.current_page - 1})">
                                    <i class="bi bi-chevron-left"></i> Prev
                                </button>
                            `;
                        }

                        if (pagination.current_page < pagination.last_page) {
                            html += `
                                <button class="btn btn-sm btn-outline-secondary" onclick="loadFingerprintAttendanceTeacher(${pagination.current_page + 1})">
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

                    // Initialize DataTable
                    if ($('#fingerprintAttendanceTableTeacher').length) {
                        $('#fingerprintAttendanceTableTeacher').DataTable({
                            pageLength: 25,
                            order: [[4, 'desc'], [5, 'desc']],
                            language: {
                                search: "Search:",
                                lengthMenu: "Show _MENU_ entries",
                                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                                infoEmpty: "No entries found",
                                infoFiltered: "(filtered from _MAX_ total entries)",
                                zeroRecords: "No matching records found"
                            },
                            responsive: true
                        });
                    }
                },
                error: function(xhr) {
                    let msg = 'Failed to load external attendance.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    container.html(`
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-exclamation-triangle"></i> ${msg}
                        </div>
                    `);
                }
            });
        }

        // Search Attendance
        $('#searchAttendanceBtn').on('click', function() {
            var date = $('#searchAttendanceDate').val();
            var status = $('#searchAttendanceStatus').val();
            loadCollectedAttendance(date, status);
        });

        // Load collected attendance when tab is shown
        $('#collected-attendance-tab').on('shown.bs.tab', function() {
            loadCollectedAttendance();
        });

        // Edit Attendance
        $(document).on('click', '.edit-attendance-btn', function() {
            var attendanceID = $(this).data('attendance-id');
            $.ajax({
                url: '{{ url("get_attendance") }}/' + attendanceID,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.attendance) {
                        var attendance = response.attendance;
                        $('#edit_attendanceID').val(attendance.attendanceID);
                        $('#edit_attendance_date').val(attendance.attendance_date);
                        $('#edit_attendance_status').val(attendance.status);
                        $('#edit_attendance_remark').val(attendance.remark || '');
                        $('#editAttendanceModal').modal('show');
                    }
                }
            });
        });

        // Edit Attendance Form
        $('#editAttendanceForm').on('submit', function(e) {
            e.preventDefault();

            var formData = $(this).serialize();
            var submitBtn = $(this).find('button[type="submit"]');
            var originalText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

            $.ajax({
                url: '{{ route("update_attendance") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(response) {
                    submitBtn.prop('disabled', false).html(originalText);
                    if (response.success) {
                        Swal.fire('Success', response.message || 'Attendance updated successfully', 'success')
                            .then(function() {
                                $('#editAttendanceModal').modal('hide');
                                loadCollectedAttendance();
                            });
                    } else {
                        Swal.fire('Error', response.message || 'Failed to update attendance', 'error');
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html(originalText);
                    var errorMsg = 'Network error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        });


        // Generate Attendance Overview
        $('#generateOverviewBtn').on('click', function() {
            var searchType = $('#overviewSearchType').val();
            var searchDate = $('#overviewSearchDate').val();

            if (!searchDate) {
                Swal.fire('Error', 'Please select a date', 'error');
                return;
            }

            if (!subclassID) {
                Swal.fire('Error', 'Class ID not found', 'error');
                return;
            }

            $('#attendanceOverviewContent').html('<div class="text-center"><div class="spinner-border text-primary-custom" role="status"></div></div>');

            $.ajax({
                url: '{{ route("get_attendance_overview") }}',
                type: 'GET',
                data: {
                    subclassID: subclassID,
                    searchType: searchType,
                    searchDate: searchDate
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displayAttendanceOverview(response.data, searchType);
                        generateAttendanceCharts(response.data, searchType);
                    } else {
                        $('#attendanceOverviewContent').html('<div class="alert alert-danger">' + (response.message || 'Failed to generate overview') + '</div>');
                    }
                },
                error: function(xhr) {
                    $('#attendanceOverviewContent').html('<div class="alert alert-danger">Error loading attendance overview</div>');
                }
            });
        });

        // Display Attendance Overview
        function displayAttendanceOverview(data, searchType) {
            var html = '<div class="row mb-3">';
            html += '<div class="col-md-3"><div class="card bg-success"><div class="card-body text-center" style="color: #ffffff;"><h4 style="color: #ffffff;">' + (data.present || 0) + '</h4><p class="mb-0" style="color: #ffffff;">Present</p></div></div></div>';
            html += '<div class="col-md-3"><div class="card bg-danger"><div class="card-body text-center" style="color: #ffffff;"><h4 style="color: #ffffff;">' + (data.absent || 0) + '</h4><p class="mb-0" style="color: #ffffff;">Absent</p></div></div></div>';
            html += '<div class="col-md-3"><div class="card bg-warning"><div class="card-body text-center" style="color: #ffffff;"><h4 style="color: #ffffff;">' + (data.late || 0) + '</h4><p class="mb-0" style="color: #ffffff;">Late</p></div></div></div>';
            html += '<div class="col-md-3"><div class="card bg-info"><div class="card-body text-center" style="color: #ffffff;"><h4 style="color: #ffffff;">' + (data.excused || 0) + '</h4><p class="mb-0" style="color: #ffffff;">Excused</p></div></div></div>';
            html += '</div>';

            html += '<div class="card mb-3">';
            html += '<div class="card-header bg-primary-custom text-white"><h6 class="mb-0">Summary</h6></div>';
            html += '<div class="card-body">';
            html += '<p><strong>Total Students:</strong> ' + (data.total_students || 0) + '</p>';
            html += '<p><strong>Total Present:</strong> ' + (data.present || 0) + '</p>';
            html += '<p><strong>Total Absent:</strong> ' + (data.absent || 0) + '</p>';
            html += '<p><strong>Attendance Rate:</strong> ' + (data.attendance_rate || 0) + '%</p>';
            html += '</div></div>';

            $('#attendanceOverviewContent').html(html);

            // Build attendance overview table under the charts (one row per period)
            displayAttendanceOverviewTable(data, searchType);
        }

        // Display Attendance Overview Table (below charts)
        function displayAttendanceOverviewTable(data, searchType) {
            var labels = data.chart_labels || [];
            var presentData = data.chart_present || [];
            var absentData = data.chart_absent || [];

            if (!labels || labels.length === 0) {
                $('#attendanceOverviewTableContainer').html('');
                return;
            }

            var html = '<div class="card">';
            html += '<div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center">';
            html += '<h6 class="mb-0"><i class="bi bi-table"></i> Attendance Records</h6>';
            html += '</div>';
            html += '<div class="card-body">';
            html += '<div class="table-responsive">';
            html += '<table class="table table-striped table-hover mb-0">';
            html += '<thead class="bg-light">';
            html += '<tr>';
            html += '<th>#</th>';
            html += '<th>Date</th>';
            html += '<th>Present</th>';
            html += '<th>Absent</th>';
            html += '<th>Total</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';

            for (var i = 0; i < labels.length; i++) {
                var dateLabel = labels[i];
                var present = presentData[i] || 0;
                var absent = absentData[i] || 0;
                var total = present + absent;

                html += '<tr>';
                html += '<td>' + (i + 1) + '</td>';
                html += '<td>' + dateLabel + '</td>';
                html += '<td><span class="badge bg-success">' + present + '</span></td>';
                html += '<td><span class="badge bg-danger">' + absent + '</span></td>';
                html += '<td>' + total + '</td>';
                html += '</tr>';
            }

            html += '</tbody>';
            html += '</table>';
            html += '</div>';
            html += '</div>';
            html += '</div>';

            $('#attendanceOverviewTableContainer').html(html);
        }

        // Generate Attendance Charts
        var attendanceChart = null;
        var statusChart = null;

        function generateAttendanceCharts(data, searchType) {
            // Destroy existing charts if they exist
            if (attendanceChart) {
                attendanceChart.destroy();
            }
            if (statusChart) {
                statusChart.destroy();
            }

            // Check if Chart.js is available
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js is not loaded. Please include Chart.js library.');
                return;
            }

            var ctx1 = document.getElementById('attendanceChart');
            var ctx2 = document.getElementById('statusChart');

            if (!ctx1 || !ctx2) return;

            // Attendance Chart (Bar Chart)
            attendanceChart = new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: data.chart_labels || [],
                    datasets: [{
                        label: 'Present',
                        data: data.chart_present || [],
                        backgroundColor: '#28a745'
                    }, {
                        label: 'Absent',
                        data: data.chart_absent || [],
                        backgroundColor: '#dc3545'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Status Distribution Chart (Pie Chart)
            statusChart = new Chart(ctx2, {
                type: 'pie',
                data: {
                    labels: ['Present', 'Absent', 'Late', 'Excused'],
                    datasets: [{
                        data: [
                            data.present || 0,
                            data.absent || 0,
                            data.late || 0,
                            data.excused || 0
                        ],
                        backgroundColor: [
                            '#28a745',
                            '#dc3545',
                            '#ffc107',
                            '#17a2b8'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        // Load Teacher Class Subjects
        function loadTeacherClassSubjects() {
            var url = '';
            var requestData = {};
            
            if (isCoordinatorView && classID) {
                // Coordinator view - load subjects for all subclasses in main class
                url = "{{ url('get_class_subjects_by_subclass') }}/0";
                requestData = {
                    classID: classID,
                    coordinator: 'true'
                };
            } else if (subclassID) {
                // Class teacher view - load subjects for specific subclass
                url = "{{ url('get_class_subjects_by_subclass') }}/" + subclassID;
            } else {
                $('#teacherSubjectsContainer').html(
                    '<div class="alert alert-danger">Class ID not found.</div>'
                );
                return;
            }

            $('#teacherSubjectsContainer').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading subjects...</p>
                </div>
            `);

            $.ajax({
                url: url,
                type: "GET",
                data: requestData,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success && response.subjects) {
                        var html = '<div class="table-responsive">';
                        html += '<table class="table table-hover table-bordered" id="teacherSubjectsTable">';
                        html += '<thead class="bg-primary-custom text-white">';
                        html += '<tr>';
                        html += '<th>#</th>';
                        html += '<th>Subject Name</th>';
                        if (isCoordinatorView && classID) {
                            html += '<th>Subclass</th>';
                        }
                        html += '<th>Student Status</th>';
                        html += '<th>Election Stats</th>';
                        html += '<th>Actions</th>';
                        html += '</tr>';
                        html += '</thead>';
                        html += '<tbody>';

                        if (response.subjects.length > 0) {
                            response.subjects.forEach(function(subject, index) {
                                html += '<tr>';
                                html += '<td>' + (index + 1) + '</td>';
                                html += '<td><strong>' + (subject.subject_name || 'N/A') + '</strong></td>';
                                if (isCoordinatorView && classID) {
                                    html += '<td>' + (subject.subclass_display || 'N/A') + '</td>';
                                }
                                
                                // Student Status Badge
                                var studentStatusBadge = '';
                                if (subject.student_status === 'Required') {
                                    studentStatusBadge = '<span class="badge bg-warning text-dark">Required</span>';
                                } else if (subject.student_status === 'Optional') {
                                    studentStatusBadge = '<span class="badge bg-info text-white">Optional</span>';
                                } else {
                                    studentStatusBadge = '<span class="badge bg-secondary">Not Set</span>';
                                }
                                html += '<td>' + studentStatusBadge + '</td>';
                                
                                // Election Stats
                                var electionStats = '';
                                if (subject.student_status === 'Optional') {
                                    var electedCount = subject.elected_count || 0;
                                    var nonElectedCount = subject.non_elected_count || 0;
                                    var totalStudents = subject.total_students || 0;
                                    electionStats = '<div class="small">';
                                    electionStats += '<span class="badge bg-success me-1">Elected: ' + electedCount + '</span>';
                                    electionStats += '<span class="badge bg-secondary">Not Elected: ' + nonElectedCount + '</span>';
                                    electionStats += '<div class="mt-1"><small class="text-muted">Total: ' + totalStudents + ' students</small></div>';
                                    electionStats += '</div>';
                                } else {
                                    electionStats = '<span class="text-muted small">N/A</span>';
                                }
                                html += '<td>' + electionStats + '</td>';
                                
                                // Actions
                                html += '<td>';
                                html += '<div class="d-flex gap-1">';
                                
                                // Election button for optional subjects
                                if (subject.student_status === 'Optional' && subject.class_subjectID) {
                                    var subjectSubclassID = isCoordinatorView && classID ? (subject.subclassID || '') : subclassID;
                                    html += '<button class="btn btn-sm btn-primary election-subject-btn" ';
                                    html += 'data-class-subject-id="' + subject.class_subjectID + '" ';
                                    html += 'data-subclass-id="' + subjectSubclassID + '" ';
                                    html += 'data-subject-name="' + (subject.subject_name || 'N/A') + '" ';
                                    html += 'title="Manage Subject Election">';
                                    html += '<i class="bi bi-person-check"></i> Election';
                                    html += '</button>';
                                }
                                
                                html += '</div>';
                                html += '</td>';
                                html += '</tr>';
                            });
                        } else {
                            var colspan = isCoordinatorView && classID ? 6 : 5;
                            html += '<tr><td colspan="' + colspan + '" class="text-center">No subjects found for this class.</td></tr>';
                        }

                        html += '</tbody>';
                        html += '</table>';
                        html += '</div>';

                        $('#teacherSubjectsContainer').html(html);

                        // Initialize DataTable
                        if ($.fn.DataTable) {
                            $('#teacherSubjectsTable').DataTable({
                                "pageLength": 25,
                                "order": [[1, "asc"]],
                                "language": {
                                    "search": "Search subjects:"
                                }
                            });
                        }
                    } else {
                        $('#teacherSubjectsContainer').html(
                            '<div class="alert alert-info text-center">' +
                            '<i class="bi bi-info-circle"></i> No subjects found for this class.' +
                            '</div>'
                        );
                    }
                },
                error: function(xhr) {
                    console.error('Error loading subjects:', xhr);
                    $('#teacherSubjectsContainer').html(
                        '<div class="alert alert-danger">' +
                        '<i class="bi bi-exclamation-triangle"></i> Failed to load subjects. Please try again.' +
                        '</div>'
                    );
                }
            });
        }

        // Handle Election Button Click (Teacher)
        $(document).on('click', '#teacherSubjectsContainer .election-subject-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var classSubjectID = $(this).data('class-subject-id');
            var subjectName = $(this).data('subject-name');
            var subclassID = $(this).data('subclass-id');

            console.log('Teacher Election clicked for classSubjectID:', classSubjectID, 'subjectName:', subjectName);

            // Set modal title
            $('#teacherElectionSubjectTitle').text(subjectName + ' Election');

            // Show loading
            $('#teacherElectionStudentsContainer').html(
                '<div class="text-center py-4">' +
                '<div class="spinner-border text-primary-custom" role="status">' +
                '<span class="visually-hidden">Loading...</span>' +
                '</div>' +
                '<p class="mt-2">Loading students...</p>' +
                '</div>'
            );

            // Store classSubjectID for save button
            $('#teacherSaveElectionBtn').data('class-subject-id', classSubjectID);
            $('#teacherSaveElectionBtn').data('subclass-id', subclassID);

            // Open modal
            $('#teacherSubjectElectionModal').modal('show');

            // Load students for election
            loadTeacherStudentsForElection(classSubjectID, subclassID);
        });

        // Function to load students for election (Teacher)
        function loadTeacherStudentsForElection(classSubjectID, subclassID) {
            $.ajax({
                url: "{{ url('get_subclass_students') }}/" + subclassID,
                type: "GET",
                dataType: 'json',
                success: function(response) {
                    console.log('Students data received:', response);

                    if (response.success && response.students && response.students.length > 0) {
                        // Fetch already elected students
                        $.ajax({
                            url: "{{ url('get_subject_electors') }}/" + classSubjectID,
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
                                html += '<table class="table table-hover table-bordered" id="teacherElectionStudentsTable">';
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
                                        html += '<button class="btn btn-sm btn-danger deselect-student-btn" ';
                                        html += 'data-student-id="' + student.studentID + '" ';
                                        html += 'data-class-subject-id="' + classSubjectID + '" ';
                                        html += 'data-student-name="' + student.first_name + ' ' + student.last_name + '" ';
                                        html += 'title="Deselect Student">';
                                        html += '<i class="bi bi-x-circle"></i> Deselect';
                                        html += '</button>';
                                    } else {
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

                                $('#teacherElectionStudentsContainer').html(html);

                                // Initialize DataTable
                                if ($.fn.DataTable) {
                                    $('#teacherElectionStudentsTable').DataTable({
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
                                var electedStudentIDs = [];
                                
                                var html = '<div class="table-responsive">';
                                html += '<table class="table table-hover table-bordered" id="teacherElectionStudentsTable">';
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
                                    html += '<tr data-student-id="' + student.studentID + '">';
                                    html += '<td>' + (index + 1) + '</td>';
                                    html += '<td>' + student.first_name + ' ' + student.last_name + '</td>';
                                    html += '<td>' + (student.admission_number || 'N/A') + '</td>';
                                    html += '<td style="text-align: center;">';
                                    html += '<input type="checkbox" class="form-check-input election-checkbox" ';
                                    html += 'data-student-id="' + student.studentID + '" ';
                                    html += 'value="' + student.studentID + '" ';
                                    html += '>';
                                    html += '</td>';
                                    html += '</tr>';
                                });

                                html += '</tbody>';
                                html += '</table>';
                                html += '</div>';

                                $('#teacherElectionStudentsContainer').html(html);

                                if ($.fn.DataTable) {
                                    $('#teacherElectionStudentsTable').DataTable({
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
                        $('#teacherElectionStudentsContainer').html(
                            '<div class="alert alert-info text-center">' +
                            '<i class="bi bi-info-circle"></i> No students found in this subclass.' +
                            '</div>'
                        );
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching students:', xhr);
                    $('#teacherElectionStudentsContainer').html(
                        '<div class="alert alert-danger">' +
                        '<i class="bi bi-exclamation-triangle"></i> Failed to load students. Please try again.' +
                        '</div>'
                    );
                }
            });
        }

        // Handle Save Election Button Click (Teacher)
        $(document).on('click', '#teacherSaveElectionBtn', function(e) {
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

            var selectedStudents = [];
            $('#teacherElectionStudentsContainer .election-checkbox:checked').each(function() {
                var $row = $(this).closest('tr');
                if ($row.find('.deselect-student-btn').length === 0) {
                    selectedStudents.push($(this).val());
                }
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
                            $('#teacherSubjectElectionModal').modal('hide');
                            if ($('#subjectManagementModal').hasClass('show')) {
                                loadTeacherClassSubjects();
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

        // Handle Deselect Student Button Click (Teacher)
        $(document).on('click', '#teacherSubjectElectionModal .deselect-student-btn', function(e) {
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
                                    var $row = $btn.closest('tr');
                                    var $td = $row.find('td:last');
                                    $td.html(
                                        '<input type="checkbox" class="form-check-input election-checkbox" ' +
                                        'data-student-id="' + studentID + '" ' +
                                        'value="' + studentID + '">'
                                    );
                                    
                                    if ($('#subjectManagementModal').hasClass('show')) {
                                        loadTeacherClassSubjects();
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
    });
    
    // Export Student Details to PDF
    $(document).on('click', '.export-student-pdf-btn', function() {
        if (!window.currentStudentDetailsData) {
            Swal.fire('Error', 'Student data not available', 'error');
            return;
        }
        
        var studentData = window.currentStudentDetailsData;
        
        // Check if jsPDF is available
        var jsPDFLib = window.jspdf || window.jsPDF;
        var JSPDF = null;
        
        if (jsPDFLib && jsPDFLib.jsPDF) {
            JSPDF = jsPDFLib.jsPDF;
        } else if (typeof jsPDF !== 'undefined') {
            JSPDF = jsPDF;
        } else if (typeof window.jsPDF !== 'undefined') {
            JSPDF = window.jsPDF;
        }
        
        if (!JSPDF) {
            Swal.fire('Error', 'PDF library not loaded. Please refresh the page.', 'error');
            return;
        }
        
        try {
            var doc = new JSPDF('p', 'mm', 'a4');
            var pageWidth = doc.internal.pageSize.getWidth();
            var margin = 15;
            var yPos = margin;
            var lineHeight = 7;
            
            // Get school info
            var schoolName = '{{ isset($school_details) ? $school_details->school_name : "School" }}';
            var schoolLogo = '{{ isset($school_details) ? $school_details->school_logo : "" }}';
            var logoUrl = schoolLogo ? '{{ asset("logos") }}/' + schoolLogo : null;
            
            // Load logo if available
            var logoPromise = Promise.resolve(null);
            if (logoUrl) {
                logoPromise = new Promise(function(resolve) {
                    var img = new Image();
                    img.crossOrigin = 'anonymous';
                    img.onload = function() { resolve(img); };
                    img.onerror = function() { resolve(null); };
                    img.src = logoUrl + (logoUrl.indexOf('?') > -1 ? '&' : '?') + 't=' + Date.now();
                });
            }
            
            // Load student photo if available
            var photoPromise = Promise.resolve(null);
            if (studentData.photo) {
                var photoUrl = '{{ asset("userImages/") }}/' + studentData.photo;
                photoPromise = new Promise(function(resolve) {
                    var img = new Image();
                    img.crossOrigin = 'anonymous';
                    img.onload = function() { resolve(img); };
                    img.onerror = function() { resolve(null); };
                    img.src = photoUrl + (photoUrl.indexOf('?') > -1 ? '&' : '?') + 't=' + Date.now();
                });
            }
            
            Promise.all([logoPromise, photoPromise]).then(function(images) {
                var logo = images[0];
                var photo = images[1];
                
                // Header with logo
                if (logo) {
                    doc.addImage(logo, 'PNG', margin, yPos, 20, 20);
                }
                doc.setFontSize(16);
                doc.setFont('helvetica', 'bold');
                doc.text(schoolName, logo ? margin + 25 : margin, yPos + 10);
                doc.setFontSize(12);
                doc.setFont('helvetica', 'normal');
                doc.text('Student Details Report', logo ? margin + 25 : margin, yPos + 16);
                yPos += 30;
                
                // Student Photo
                if (photo) {
                    var photoSize = 40;
                    doc.addImage(photo, 'PNG', pageWidth - margin - photoSize, margin, photoSize, photoSize);
                }
                
                // Student Information
                doc.setFontSize(14);
                doc.setFont('helvetica', 'bold');
                doc.text('Student Information', margin, yPos);
                yPos += lineHeight + 2;
                
                doc.setFontSize(10);
                doc.setFont('helvetica', 'normal');
                
                var details = [
                    ['Admission Number:', studentData.admission_number],
                    ['Full Name:', studentData.full_name],
                    ['Gender:', studentData.gender],
                    ['Date of Birth:', studentData.date_of_birth],
                    ['Admission Date:', studentData.admission_date],
                    ['Parent/Guardian:', studentData.parent_name],
                    ['Address:', studentData.address],
                    ['Status:', studentData.status]
                ];
                
                details.forEach(function(detail) {
                    doc.setFont('helvetica', 'bold');
                    doc.text(detail[0], margin, yPos);
                    doc.setFont('helvetica', 'normal');
                    doc.text(detail[1] || 'N/A', margin + 50, yPos);
                    yPos += lineHeight;
                    
                    if (yPos > 270) {
                        doc.addPage();
                        yPos = margin;
                    }
                });
                
                // Footer
                var pageCount = doc.internal.getNumberOfPages();
                for (var i = 1; i <= pageCount; i++) {
                    doc.setPage(i);
                    doc.setFontSize(8);
                    doc.text('Generated on: ' + new Date().toLocaleString(), margin, doc.internal.pageSize.getHeight() - 10);
                    doc.text('Page ' + i + ' of ' + pageCount, pageWidth - margin - 20, doc.internal.pageSize.getHeight() - 10);
                }
                
                // Save PDF
                var fileName = 'Student_' + studentData.admission_number.replace(/\s+/g, '_') + '_' + new Date().toISOString().split('T')[0] + '.pdf';
                doc.save(fileName);
                
                Swal.fire('Success', 'PDF exported successfully', 'success');
            });
        } catch (error) {
            console.error('PDF Export Error:', error);
            Swal.fire('Error', 'Failed to export PDF: ' + error.message, 'error');
        }
    });
    
    // Export Student Details to Excel
    $(document).on('click', '.export-student-excel-btn', function() {
        if (!window.currentStudentDetailsData) {
            Swal.fire('Error', 'Student data not available', 'error');
            return;
        }
        
        // Check if XLSX is available
        if (typeof XLSX === 'undefined') {
            Swal.fire('Error', 'Excel library not loaded. Please refresh the page.', 'error');
            return;
        }
        
        var studentData = window.currentStudentDetailsData;
        
        try {
            // Create workbook
            var wb = XLSX.utils.book_new();
            
            // Prepare data
            var data = [
                ['Student Details Report'],
                [''],
                ['Admission Number', studentData.admission_number],
                ['Full Name', studentData.full_name],
                ['First Name', studentData.first_name],
                ['Middle Name', studentData.middle_name || ''],
                ['Last Name', studentData.last_name],
                ['Gender', studentData.gender],
                ['Date of Birth', studentData.date_of_birth],
                ['Admission Date', studentData.admission_date],
                ['Parent/Guardian', studentData.parent_name],
                ['Address', studentData.address],
                ['Status', studentData.status],
                [''],
                ['Generated on', new Date().toLocaleString()]
            ];
            
            // Create worksheet
            var ws = XLSX.utils.aoa_to_sheet(data);
            
            // Set column widths
            ws['!cols'] = [
                { wch: 20 },
                { wch: 30 }
            ];
            
            // Add worksheet to workbook
            XLSX.utils.book_append_sheet(wb, ws, 'Student Details');
            
            // Generate filename
            var fileName = 'Student_' + studentData.admission_number.replace(/\s+/g, '_') + '_' + new Date().toISOString().split('T')[0] + '.xlsx';
            
            // Save file
            XLSX.writeFile(wb, fileName);
            
            Swal.fire('Success', 'Excel file exported successfully', 'success');
        } catch (error) {
            console.error('Excel Export Error:', error);
            Swal.fire('Error', 'Failed to export Excel: ' + error.message, 'error');
        }
    });
    
    // Export Students List to PDF
    $(document).on('click', '#exportStudentsPdfBtn', function(e) {
        e.preventDefault();
        console.log('Export PDF clicked');
        console.log('window.allStudentsForExport:', window.allStudentsForExport);
        
        if (!window.allStudentsForExport || window.allStudentsForExport.length === 0) {
            console.error('No students data available');
            Swal.fire('Error', 'No students data available to export. Please load students first.', 'error');
            return;
        }
        
        var students = window.allStudentsForExport;
        var isCoordinatorView = {{ isset($isCoordinatorView) && $isCoordinatorView ? 'true' : 'false' }};
        var className = isCoordinatorView && classID ? '{{ isset($selectedClass) ? $selectedClass->class_name : "Class" }}' : '{{ isset($subclassDisplayName) ? $subclassDisplayName : "Class" }}';
        
        // Check if jsPDF is available
        var jsPDFLib = window.jspdf || window.jsPDF;
        var JSPDF = null;
        
        if (jsPDFLib && jsPDFLib.jsPDF) {
            JSPDF = jsPDFLib.jsPDF;
        } else if (typeof jsPDF !== 'undefined') {
            JSPDF = jsPDF;
        } else if (typeof window.jsPDF !== 'undefined') {
            JSPDF = window.jsPDF;
        }
        
        if (!JSPDF) {
            Swal.fire('Error', 'PDF library not loaded. Please refresh the page.', 'error');
            return;
        }
        
        try {
            var doc = new JSPDF('p', 'mm', 'a4');
            var pageWidth = doc.internal.pageSize.getWidth();
            var pageHeight = doc.internal.pageSize.getHeight();
            var margin = 15;
            var yPos = margin;
            
            // Calculate available width for tables (used for both stats and main table)
            var availableWidth = pageWidth - (margin * 2);
            
            // Get school info
            var schoolName = '{{ isset($school_details) ? $school_details->school_name : "School" }}';
            var schoolLogo = '{{ isset($school_details) && $school_details->school_logo ? asset($school_details->school_logo) : "" }}';
            var logoUrl = schoolLogo || null;
            
            // Load logo if available
            var logoPromise = Promise.resolve(null);
            if (logoUrl) {
                logoPromise = new Promise(function(resolve) {
                    var img = new Image();
                    img.crossOrigin = 'anonymous';
                    img.onload = function() { resolve(img); };
                    img.onerror = function() { resolve(null); };
                    img.src = logoUrl + (logoUrl.indexOf('?') > -1 ? '&' : '?') + 't=' + Date.now();
                });
            }
            
            Promise.all([logoPromise]).then(function(images) {
                var logo = images[0];
                
                // Header with logo
                if (logo) {
                    try {
                        doc.addImage(logo, 'PNG', margin, yPos, 20, 20);
                    } catch(e) {
                        console.warn('Failed to add logo:', e);
                    }
                }
                
                // School name (centered, with color #940000)
                doc.setFontSize(18);
                doc.setTextColor(148, 0, 0); // #940000
                doc.setFont('helvetica', 'bold');
                var centerX = pageWidth / 2;
                doc.text(schoolName.toUpperCase(), centerX, yPos + 12, { align: 'center' });
                
                // Title - Use className variable (already defined above) in CAPS
                var displayClassName = className && className !== 'Class' ? className.toUpperCase().trim() : '';
                
                // Title with class name and "STUDENT INFORMATION" (all caps, no "CLASS" prefix)
                doc.setFontSize(14);
                doc.setTextColor(0, 0, 0);
                doc.setFont('helvetica', 'bold');
                var titleText = displayClassName ? (displayClassName + ' STUDENT INFORMATION') : 'STUDENT INFORMATION';
                doc.text(titleText, centerX, yPos + 20, { align: 'center' });
                yPos += 30;
                
                // Filter only active students (for both statistics and table)
                var activeStudents = students.filter(function(s) {
                    return s.status === 'Active' || s.status === 'active' || !s.status;
                });
                
                // Statistics in tabular form (only active students)
                var totalStudents = activeStudents.length;
                var maleCount = activeStudents.filter(function(s) { return s.gender === 'Male'; }).length;
                var femaleCount = activeStudents.filter(function(s) { return s.gender === 'Female'; }).length;
                var healthIssuesCount = activeStudents.filter(function(s) { return s.has_health_issues; }).length;
                
                // Create statistics table using autoTable
                if (typeof doc.autoTable !== 'undefined') {
                    doc.autoTable({
                        startY: yPos,
                        head: [['Total Students', 'Male', 'Female', 'Health Issues']],
                        body: [[totalStudents.toString(), maleCount.toString(), femaleCount.toString(), healthIssuesCount.toString()]],
                        theme: 'plain',
                        headStyles: { 
                            fillColor: [148, 0, 0], // #940000
                            textColor: 255, 
                            fontStyle: 'bold',
                            fontSize: 10
                        },
                        bodyStyles: {
                            fontSize: 10,
                            fontStyle: 'bold'
                        },
                        margin: { left: margin, right: margin },
                        tableWidth: availableWidth,
                        columnStyles: {
                            0: { cellWidth: availableWidth * 0.25, halign: 'center' },
                            1: { cellWidth: availableWidth * 0.25, halign: 'center' },
                            2: { cellWidth: availableWidth * 0.25, halign: 'center' },
                            3: { cellWidth: availableWidth * 0.25, halign: 'center' }
                        },
                        styles: { cellPadding: 5 }
                    });
                    
                    yPos = doc.lastAutoTable.finalY + 10;
                } else {
                    // Fallback if autoTable not available
                    doc.setFontSize(10);
                    doc.text('Total Students: ' + totalStudents + ' | Male: ' + maleCount + ' | Female: ' + femaleCount + ' | Health Issues: ' + healthIssuesCount, margin, yPos);
                    yPos += 10;
                }
                
                // Prepare table data (only active students)
                var tableHeaders = ['#', 'Admission No.', 'Full Name', 'Gender', 'Admission Date', 'Parent/Guardian'];
                if (isCoordinatorView) {
                    tableHeaders.splice(4, 0, 'Subclass');
                }
                
                var tableData = [];
                activeStudents.forEach(function(student, index) {
                    var row = [
                        (index + 1).toString(),
                        student.admission_number || 'N/A',
                        student.full_name || 'N/A',
                        student.gender || 'N/A',
                        student.admission_date || 'N/A',
                        student.parent_name || 'Not Assigned'
                    ];
                    
                    if (isCoordinatorView) {
                        row.splice(4, 0, student.subclass_display || 'N/A');
                    }
                    
                    tableData.push(row);
                });
                
                // Table margin (availableWidth already defined above)
                var tableMargin = { left: margin, right: margin, top: 5, bottom: 25 };
                
                // Column styles based on coordinator view (Status column removed)
                var columnStyles = {};
                if (isCoordinatorView) {
                    columnStyles = {
                        0: { cellWidth: availableWidth * 0.05, halign: 'center' }, // #
                        1: { cellWidth: availableWidth * 0.12 }, // Admission No.
                        2: { cellWidth: availableWidth * 0.25, fontStyle: 'bold' }, // Full Name
                        3: { cellWidth: availableWidth * 0.08, halign: 'center' }, // Gender
                        4: { cellWidth: availableWidth * 0.15 }, // Subclass
                        5: { cellWidth: availableWidth * 0.12 }, // Admission Date
                        6: { cellWidth: availableWidth * 0.23 } // Parent/Guardian
                    };
                } else {
                    columnStyles = {
                        0: { cellWidth: availableWidth * 0.05, halign: 'center' }, // #
                        1: { cellWidth: availableWidth * 0.12 }, // Admission No.
                        2: { cellWidth: availableWidth * 0.30, fontStyle: 'bold' }, // Full Name
                        3: { cellWidth: availableWidth * 0.08, halign: 'center' }, // Gender
                        4: { cellWidth: availableWidth * 0.15 }, // Admission Date
                        5: { cellWidth: availableWidth * 0.30 } // Parent/Guardian
                    };
                }
                
                // Check if jsPDF-autoTable plugin is available
                if (typeof doc.autoTable !== 'undefined') {
                    // Use autoTable plugin for main students table
                    doc.autoTable({
                        startY: yPos,
                        head: [tableHeaders],
                        body: tableData,
                        theme: 'striped',
                        headStyles: { 
                            fillColor: [148, 0, 0], // #940000
                            textColor: 255, 
                            fontStyle: 'bold',
                            fontSize: 9
                        },
                        styles: { 
                            fontSize: 8,
                            cellPadding: 3
                        },
                        margin: tableMargin,
                        tableWidth: availableWidth,
                        columnStyles: columnStyles,
                        overflow: 'linebreak',
                        showHead: 'everyPage',
                        didDrawPage: function(data) {
                            // Footer on each page
                            var pageNumber = data.pageNumber;
                            var totalPages = doc.internal.getNumberOfPages();
                            var footerY = pageHeight - 8;
                            var dateY = footerY - 5;
                            
                            // Generated date
                            doc.setFontSize(8);
                            doc.setTextColor(100, 100, 100);
                            doc.text('Generated on: ' + new Date().toLocaleString(), centerX, dateY, { align: 'center' });
                            
                            // Page number
                            doc.text('Page ' + pageNumber + ' of ' + totalPages, pageWidth - tableMargin.right - 5, pageHeight - 10, { align: 'right' });
                            
                            // Powered by: EmCa Technologies LTD
                            doc.setFontSize(8);
                            doc.setTextColor(148, 0, 0); // #940000
                            doc.setFont('helvetica', 'bold');
                            doc.text('Powered by: EmCa Technologies LTD', centerX, footerY, { align: 'center' });
                        }
                    });
                } else {
                    // Fallback: Show error if autoTable is not available
                    Swal.fire('Error', 'Table plugin not loaded. Please refresh the page.', 'error');
                    return;
                }
                
                // Save PDF
                var fileName = 'Students_' + className.replace(/\s+/g, '_') + '_' + new Date().toISOString().split('T')[0] + '.pdf';
                doc.save(fileName);
                
                Swal.fire('Success', 'PDF exported successfully', 'success');
            });
        } catch (error) {
            console.error('PDF Export Error:', error);
            Swal.fire('Error', 'Failed to export PDF: ' + error.message, 'error');
        }
    });
    
    // Export Students List to Excel
    $(document).on('click', '#exportStudentsExcelBtn', function(e) {
        e.preventDefault();
        console.log('Export Excel clicked');
        console.log('window.allStudentsForExport:', window.allStudentsForExport);
        
        if (!window.allStudentsForExport || window.allStudentsForExport.length === 0) {
            console.error('No students data available');
            Swal.fire('Error', 'No students data available to export. Please load students first.', 'error');
            return;
        }
        
        // Check if XLSX is available
        if (typeof XLSX === 'undefined') {
            Swal.fire('Error', 'Excel library not loaded. Please refresh the page.', 'error');
            return;
        }
        
        var students = window.allStudentsForExport;
        var isCoordinatorView = {{ isset($isCoordinatorView) && $isCoordinatorView ? 'true' : 'false' }};
        var className = isCoordinatorView && classID ? '{{ isset($selectedClass) ? $selectedClass->class_name : "Class" }}' : '{{ isset($subclassDisplayName) ? $subclassDisplayName : "Class" }}';
        
        try {
            // Create workbook
            var wb = XLSX.utils.book_new();
            
            // Prepare data
            var data = [
                ['Students List - ' + className],
                [''],
                ['Total Students', students.length],
                ['Male Students', students.filter(function(s) { return s.gender === 'Male'; }).length],
                ['Female Students', students.filter(function(s) { return s.gender === 'Female'; }).length],
                ['Health Issues', students.filter(function(s) { return s.has_health_issues; }).length],
                [''],
                ['#', 'Admission Number', 'First Name', 'Middle Name', 'Last Name', 'Gender', 'Date of Birth', 'Admission Date', 'Parent/Guardian', 'Address', 'Status']
            ];
            
            if (isCoordinatorView) {
                data[7].splice(7, 0, 'Subclass');
            }
            
            // Add student rows
            students.forEach(function(student, index) {
                var row = [
                    index + 1,
                    student.admission_number,
                    student.first_name,
                    student.middle_name || '',
                    student.last_name,
                    student.gender,
                    student.date_of_birth,
                    student.admission_date,
                    student.parent_name,
                    student.address,
                    student.status
                ];
                
                if (isCoordinatorView) {
                    row.splice(7, 0, student.subclass_display);
                }
                
                data.push(row);
            });
            
            data.push(['']);
            data.push(['Generated on', new Date().toLocaleString()]);
            
            // Create worksheet
            var ws = XLSX.utils.aoa_to_sheet(data);
            
            // Set column widths
            var colWidths = isCoordinatorView 
                ? [{ wch: 5 }, { wch: 15 }, { wch: 15 }, { wch: 15 }, { wch: 15 }, { wch: 10 }, { wch: 12 }, { wch: 12 }, { wch: 25 }, { wch: 30 }, { wch: 15 }, { wch: 10 }]
                : [{ wch: 5 }, { wch: 15 }, { wch: 15 }, { wch: 15 }, { wch: 15 }, { wch: 10 }, { wch: 12 }, { wch: 12 }, { wch: 25 }, { wch: 30 }, { wch: 10 }];
            ws['!cols'] = colWidths;
            
            // Add worksheet to workbook
            XLSX.utils.book_append_sheet(wb, ws, 'Students');
            
            // Generate filename
            var fileName = 'Students_' + className.replace(/\s+/g, '_') + '_' + new Date().toISOString().split('T')[0] + '.xlsx';
            
            // Save file
            XLSX.writeFile(wb, fileName);
            
            Swal.fire('Success', 'Excel file exported successfully', 'success');
        } catch (error) {
            console.error('Excel Export Error:', error);
            Swal.fire('Error', 'Failed to export Excel: ' + error.message, 'error');
        }
    });
})(jQuery);
</script>

<!-- Chart.js Library for Attendance Charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<!-- jsPDF Library for PDF generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<!-- jsPDF AutoTable plugin for tables in PDF -->
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.5.31/dist/jspdf.plugin.autotable.min.js"></script>
<script>
    // Ensure jsPDF is available globally
    if (typeof window.jspdf !== 'undefined' && !window.jsPDF) {
        window.jsPDF = window.jspdf.jsPDF;
    }

    // SheetJS for Excel export (if not already loaded)
    if (typeof XLSX === 'undefined') {
        var sheetJsScript = document.createElement('script');
        sheetJsScript.src = 'https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js';
        document.head.appendChild(sheetJsScript);
    }
</script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
