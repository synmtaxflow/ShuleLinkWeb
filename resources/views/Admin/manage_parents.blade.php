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
    
    .card-header-custom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }
    
    .card-title-custom {
        font-size: 1.3rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
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
                    <i class="bi bi-people-fill"></i> Manage Parents
                </h4>
                <div class="d-flex gap-2">
                   
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="parentsTable" class="table table-hover table-striped align-middle mb-0" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>Image</th>
                            <th>Full Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Number of Children</th>
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

<!-- Register Parent Modal -->
<div class="modal fade" id="addParentModal" tabindex="-1" aria-labelledby="addParentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addParentModalLabel">
                    <i class="bi bi-person-plus"></i> Register New Parent
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addParentForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                            <div class="text-danger" id="first_name_error" style="display: none;"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middle_name" name="middle_name">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                            <div class="text-danger" id="last_name_error" style="display: none;"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="255614863345" required>
                            <small class="text-muted">Format: 255 + 6 or 7 + 8 digits (e.g., 255614863345)</small>
                            <div class="text-danger" id="phone_error" style="display: none;"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                            <div class="text-danger" id="email_error" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="occupation" class="form-label">Occupation</label>
                            <input type="text" class="form-control" id="occupation" name="occupation">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="national_id" class="form-label">National ID</label>
                            <input type="text" class="form-control" id="national_id" name="national_id">
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-check-circle"></i> Register Parent
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Parent Modal -->
<div class="modal fade" id="editParentModal" tabindex="-1" aria-labelledby="editParentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editParentModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Parent
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editParentForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="edit_parentID" name="parentID">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                            <div class="text-danger" id="edit_first_name_error" style="display: none;"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="edit_middle_name" name="middle_name">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                            <div class="text-danger" id="edit_last_name_error" style="display: none;"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_gender" class="form-label">Gender</label>
                            <select class="form-select" id="edit_gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_phone" name="phone" placeholder="255614863345" required>
                            <small class="text-muted">Format: 255 + 6 or 7 + 8 digits (e.g., 255614863345)</small>
                            <div class="text-danger" id="edit_phone_error" style="display: none;"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                            <div class="text-danger" id="edit_email_error" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_occupation" class="form-label">Occupation</label>
                            <input type="text" class="form-control" id="edit_occupation" name="occupation">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_national_id" class="form-label">National ID</label>
                            <input type="text" class="form-control" id="edit_national_id" name="national_id">
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
                        <div id="current_photo_preview" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-check-circle"></i> Update Parent
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View More Modal -->
<div class="modal fade" id="viewParentModal" tabindex="-1" aria-labelledby="viewParentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewParentModalLabel">
                    <i class="bi bi-eye"></i> Parent Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewParentContent" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                <div class="text-center">
                    <div class="spinner-border text-primary-custom" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" id="signNewStudentBtn">
                    <i class="bi bi-person-plus"></i> Sign New Student
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Sign New Student Modal (will be opened from view more) -->
<div class="modal fade" id="signNewStudentModal" tabindex="-1" aria-labelledby="signNewStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="signNewStudentModalLabel">
                    <i class="bi bi-person-plus"></i> Sign New Student for <span id="signStudentParentName"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">This will redirect you to the student registration page with this parent pre-selected.</p>
                <p><strong>Parent:</strong> <span id="signStudentParentInfo"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="confirmSignStudentBtn">
                    <i class="bi bi-check-circle"></i> Continue to Student Registration
                </button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable for Parents (like manage_library style)
    var table = $('#parentsTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '{{ route("get_parents") }}',
            type: 'GET',
            dataSrc: function(json) {
                // Update student_count for each parent
                if (json.success && json.parents) {
                    json.parents.forEach(function(parent) {
                        // student_count is already included from backend
                        if (!parent.student_count) {
                            parent.student_count = 0;
                        }
                    });
                    return json.parents;
                }
                return [];
            }
        },
        columns: [
            {
                data: 'photo',
                render: function(data, type, row) {
                    var imgSrc = data || (row.gender === 'Female' ? '{{ asset("images/female.png") }}' : '{{ asset("images/male.png") }}');
                    return '<img src="' + imgSrc + '" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover; border: 3px solid #940000;" alt="Parent">';
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return '<strong>' + row.first_name + ' ' + (row.middle_name || '') + ' ' + row.last_name + '</strong>';
                }
            },
            { data: 'phone' },
            { 
                data: 'email',
                render: function(data) {
                    return data || 'N/A';
                }
            },
            {
                data: 'student_count',
                render: function(data, type, row) {
                    return '<span class="badge bg-info">' + (data || 0) + ' Child(ren)</span>';
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return '<div class="d-flex justify-content-center align-items-center gap-2">' +
                        '<button class="btn btn-sm btn-info text-white view-parent-btn" data-parent-id="' + row.parentID + '" title="View More">' +
                        '<i class="bi bi-eye-fill"></i>' +
                        '</button>' +
                        '<button class="btn btn-sm btn-warning text-dark edit-parent-btn" data-parent-id="' + row.parentID + '" title="Edit">' +
                        '<i class="bi bi-pencil-square"></i>' +
                        '</button>' +
                        '<button class="btn btn-sm btn-danger text-white delete-parent-btn" data-parent-id="' + row.parentID + '" title="Delete">' +
                        '<i class="bi bi-trash"></i>' +
                        '</button>' +
                        '</div>';
                }
            }
        ],
        order: [[1, 'asc']], // Sort by name
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        autoWidth: false, // Let CSS control width
        responsive: false, // Disable responsive mode to allow horizontal scroll
        language: {
            emptyTable: "No parents found",
            search: "Search:",
            lengthMenu: "Show _MENU_ records per page",
            info: "Showing _START_ to _END_ of _TOTAL_ records",
            infoEmpty: "No records available",
            infoFiltered: "(filtered from _MAX_ total records)",
            zeroRecords: "No matching records found"
        },
        columnDefs: [
            { "orderable": false, "targets": [5] } // Disable sorting on Actions column
        ]
    });

    // Load parents data
    function loadParents() {
        table.ajax.reload(null, false);
    }

    // Initial load
    loadParents();

    // Phone validation
    function validatePhone(phone) {
        var cleaned = phone.replace(/[^0-9]/g, '');
        return /^255[67]\d{8}$/.test(cleaned);
    }

    // Register Parent Form
    $('#addParentForm').on('submit', function(e) {
        e.preventDefault();
        
        $('.text-danger').hide().text('');
        $('.form-control').removeClass('is-invalid');
        
        var phone = $('#phone').val().replace(/[^0-9]/g, '');
        if (!validatePhone(phone)) {
            $('#phone_error').text('Phone number must be 12 digits: 255 + 6 or 7 + 8 more digits (e.g., 255614863345 or 255714863345)').show();
            $('#phone').addClass('is-invalid');
            return false;
        }
        $('#phone').val(phone);
        
        var formData = new FormData(this);
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Registering...');
        
        $.ajax({
            url: '{{ route("save_parent") }}',
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
                    Swal.fire('Success', response.message || 'Parent registered successfully', 'success')
                        .then(function() {
                            $('#addParentModal').modal('hide');
                            $('#addParentForm')[0].reset();
                            loadParents();
                        });
                } else {
                    if (response.errors) {
                        $.each(response.errors, function(key, value) {
                            $('#' + key + '_error').text(value).show();
                            $('#' + key).addClass('is-invalid');
                        });
                    }
                    Swal.fire('Error', response.message || 'Failed to register parent', 'error');
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText);
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors || {};
                    $.each(errors, function(key, value) {
                        $('#' + key + '_error').text(Array.isArray(value) ? value[0] : value).show();
                        $('#' + key).addClass('is-invalid');
                    });
                }
                Swal.fire('Error', xhr.responseJSON.message || 'Failed to register parent', 'error');
            }
        });
    });

    // Edit Parent Button
    $(document).on('click', '.edit-parent-btn', function() {
        var parentID = $(this).data('parent-id');
        
        $.ajax({
            url: '{{ route("get_parent", ":id") }}'.replace(':id', parentID),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var parent = response.parent;
                    $('#edit_parentID').val(parent.parentID);
                    $('#edit_first_name').val(parent.first_name);
                    $('#edit_middle_name').val(parent.middle_name || '');
                    $('#edit_last_name').val(parent.last_name);
                    $('#edit_gender').val(parent.gender || '');
                    $('#edit_phone').val(parent.phone);
                    $('#edit_email').val(parent.email || '');
                    $('#edit_occupation').val(parent.occupation || '');
                    $('#edit_national_id').val(parent.national_id || '');
                    $('#edit_address').val(parent.address || '');
                    
                    if (parent.photo) {
                        $('#current_photo_preview').html('<img src="' + parent.photo + '" class="img-thumbnail" style="max-width: 150px;" alt="Current Photo">');
                    } else {
                        $('#current_photo_preview').html('');
                    }
                    
                    $('#editParentModal').modal('show');
                } else {
                    Swal.fire('Error', response.message || 'Failed to load parent details', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to load parent details', 'error');
            }
        });
    });

    // Update Parent Form
    $('#editParentForm').on('submit', function(e) {
        e.preventDefault();
        
        $('.text-danger').hide().text('');
        $('.form-control').removeClass('is-invalid');
        
        var phone = $('#edit_phone').val().replace(/[^0-9]/g, '');
        if (!validatePhone(phone)) {
            $('#edit_phone_error').text('Phone number must be 12 digits: 255 + 6 or 7 + 8 more digits (e.g., 255614863345 or 255714863345)').show();
            $('#edit_phone').addClass('is-invalid');
            return false;
        }
        $('#edit_phone').val(phone);
        
        var formData = new FormData(this);
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Updating...');
        
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
                    if (response.errors) {
                        $.each(response.errors, function(key, value) {
                            $('#edit_' + key + '_error').text(value).show();
                            $('#edit_' + key).addClass('is-invalid');
                        });
                    }
                    Swal.fire('Error', response.message || 'Failed to update parent', 'error');
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText);
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors || {};
                    $.each(errors, function(key, value) {
                        $('#edit_' + key + '_error').text(Array.isArray(value) ? value[0] : value).show();
                        $('#edit_' + key).addClass('is-invalid');
                    });
                }
                Swal.fire('Error', xhr.responseJSON.message || 'Failed to update parent', 'error');
            }
        });
    });

    // View More Button
    var studentsDataTable = null;
    $(document).on('click', '.view-parent-btn', function() {
        var parentID = $(this).data('parent-id');
        
        $('#viewParentContent').html('<div class="text-center py-5"><div class="spinner-border text-primary-custom" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        $('#viewParentModal').modal('show');
        
        $.ajax({
            url: '{{ route("get_parent_details", ":id") }}'.replace(':id', parentID),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var parent = response.parent;
                    var html = '<div style="padding: 20px;">';
                    
                    // Parent Header Section (like manage_school)
                    html += '<div class="school-details-card" style="margin-bottom: 25px;">';
                    html += '<div class="school-header">';
                    html += '<div class="d-flex align-items-center">';
                    var imgSrc = parent.photo || (parent.gender === 'Female' ? '{{ asset("images/female.png") }}' : '{{ asset("images/male.png") }}');
                    html += '<div class="school-logo-preview me-3">';
                    html += '<img src="' + imgSrc + '" alt="' + parent.full_name + '">';
                    html += '</div>';
                    html += '<div>';
                    html += '<h3 class="school-title">' + parent.full_name + '</h3>';
                    html += '<small class="text-muted">Parent ID: ' + parent.parentID + '</small>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                    
                    // Parent Info Grid (like manage_school)
                    html += '<div class="school-info-grid">';
                    html += '<div class="info-item">';
                    html += '<i class="bi bi-telephone"></i>';
                    html += '<div class="info-item-content">';
                    html += '<div class="info-item-label">Phone</div>';
                    html += '<div class="info-item-value">' + parent.phone + '</div>';
                    html += '</div></div>';
                    
                    if (parent.email) {
                        html += '<div class="info-item">';
                        html += '<i class="bi bi-envelope"></i>';
                        html += '<div class="info-item-content">';
                        html += '<div class="info-item-label">Email</div>';
                        html += '<div class="info-item-value">' + parent.email + '</div>';
                        html += '</div></div>';
                    }
                    
                    html += '<div class="info-item">';
                    html += '<i class="bi bi-gender-ambiguous"></i>';
                    html += '<div class="info-item-content">';
                    html += '<div class="info-item-label">Gender</div>';
                    html += '<div class="info-item-value">' + (parent.gender || 'N/A') + '</div>';
                    html += '</div></div>';
                    
                    if (parent.occupation) {
                        html += '<div class="info-item">';
                        html += '<i class="bi bi-briefcase"></i>';
                        html += '<div class="info-item-content">';
                        html += '<div class="info-item-label">Occupation</div>';
                        html += '<div class="info-item-value">' + parent.occupation + '</div>';
                        html += '</div></div>';
                    }
                    
                    if (parent.national_id) {
                        html += '<div class="info-item">';
                        html += '<i class="bi bi-card-text"></i>';
                        html += '<div class="info-item-content">';
                        html += '<div class="info-item-label">National ID</div>';
                        html += '<div class="info-item-value">' + parent.national_id + '</div>';
                        html += '</div></div>';
                    }
                    
                    if (parent.address) {
                        html += '<div class="info-item">';
                        html += '<i class="bi bi-geo-alt"></i>';
                        html += '<div class="info-item-content">';
                        html += '<div class="info-item-label">Address</div>';
                        html += '<div class="info-item-value">' + parent.address + '</div>';
                        html += '</div></div>';
                    }
                    
                    html += '<div class="info-item">';
                    html += '<i class="bi bi-people-fill"></i>';
                    html += '<div class="info-item-content">';
                    html += '<div class="info-item-label">Active Children</div>';
                    html += '<div class="info-item-value">' + parent.student_count + '</div>';
                    html += '</div></div>';
                    
                    html += '</div>'; // End school-info-grid
                    html += '</div>'; // End school-details-card
                    
                    // Classes Section
                    if (parent.classes && parent.classes.length > 0) {
                        html += '<div class="school-details-card" style="margin-bottom: 25px;">';
                        html += '<div class="card-header-custom">';
                        html += '<h5 class="card-title-custom"><i class="bi bi-book"></i> Classes</h5>';
                        html += '</div>';
                        html += '<div class="d-flex flex-wrap gap-2">';
                        parent.classes.forEach(function(className) {
                            html += '<span class="badge bg-primary-custom fs-6 p-2">' + className + '</span>';
                        });
                        html += '</div>';
                        html += '</div>';
                    }
                    
                    // Active Students Section with DataTable
                    html += '<div class="school-details-card">';
                    html += '<div class="card-header-custom">';
                    html += '<h5 class="card-title-custom"><i class="bi bi-people"></i> Active Students (' + (parent.students ? parent.students.length : 0) + ')</h5>';
                    html += '</div>';
                    
                    if (parent.students && parent.students.length > 0) {
                        html += '<div class="table-responsive">';
                        html += '<table id="parentStudentsTable" class="table table-hover table-striped" style="width:100%">';
                        html += '<thead class="table-light">';
                        html += '<tr>';
                        html += '<th>#</th>';
                        html += '<th>Photo</th>';
                        html += '<th>Admission Number</th>';
                        html += '<th>Full Name</th>';
                        html += '<th>Class</th>';
                        html += '<th>Gender</th>';
                        html += '</tr>';
                        html += '</thead>';
                        html += '<tbody>';
                        parent.students.forEach(function(student, index) {
                            var studentImg = student.photo || (student.gender === 'Female' ? '{{ asset("images/female.png") }}' : '{{ asset("images/male.png") }}');
                            html += '<tr>';
                            html += '<td>' + (index + 1) + '</td>';
                            html += '<td><img src="' + studentImg + '" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" alt="Student"></td>';
                            html += '<td>' + student.admission_number + '</td>';
                            html += '<td>' + student.full_name + '</td>';
                            html += '<td>' + student.full_class + '</td>';
                            html += '<td>' + (student.gender || 'N/A') + '</td>';
                            html += '</tr>';
                        });
                        html += '</tbody>';
                        html += '</table>';
                        html += '</div>';
                    } else {
                        html += '<div class="alert alert-info"><i class="bi bi-info-circle"></i> No active students found for this parent.</div>';
                    }
                    html += '</div>'; // End school-details-card
                    
                    html += '</div>'; // End padding div
                    
                    $('#viewParentContent').html(html);
                    $('#signNewStudentBtn').data('parent-id', parentID);
                    $('#signStudentParentName').text(parent.full_name);
                    $('#signStudentParentInfo').text(parent.full_name + ' (' + parent.phone + ')');
                    
                    // Initialize DataTable for students if table exists
                    if (parent.students && parent.students.length > 0) {
                        setTimeout(function() {
                            if (studentsDataTable) {
                                studentsDataTable.destroy();
                            }
                            studentsDataTable = $('#parentStudentsTable').DataTable({
                                "order": [[3, "asc"]], // Sort by name
                                "pageLength": 10,
                                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                                "responsive": true,
                                "language": {
                                    "search": "Search:",
                                    "lengthMenu": "Show _MENU_ records per page",
                                    "info": "Showing _START_ to _END_ of _TOTAL_ records",
                                    "infoEmpty": "No records available",
                                    "infoFiltered": "(filtered from _MAX_ total records)",
                                    "zeroRecords": "No matching records found"
                                }
                            });
                        }, 100);
                    }
                } else {
                    $('#viewParentContent').html('<div class="alert alert-danger">' + (response.message || 'Failed to load parent details') + '</div>');
                }
            },
            error: function() {
                $('#viewParentContent').html('<div class="alert alert-danger">Failed to load parent details</div>');
            }
        });
    });
    
    // Destroy DataTable when modal is closed
    $('#viewParentModal').on('hidden.bs.modal', function() {
        if (studentsDataTable) {
            studentsDataTable.destroy();
            studentsDataTable = null;
        }
    });

    // Sign New Student Button
    $('#signNewStudentBtn').on('click', function() {
        var parentID = $(this).data('parent-id');
        $('#signNewStudentModal').data('parent-id', parentID);
        $('#viewParentModal').modal('hide');
        $('#signNewStudentModal').modal('show');
    });

    // Confirm Sign New Student
    $('#confirmSignStudentBtn').on('click', function() {
        var parentID = $('#signNewStudentModal').data('parent-id');
        // Redirect to student registration page with parent ID
        // You may need to adjust this route based on your student registration page
        window.location.href = '{{ route("ClassMangement") }}?parentID=' + parentID;
    });

    // Delete Parent Button
    $(document).on('click', '.delete-parent-btn', function() {
        var parentID = $(this).data('parent-id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("delete_parent", ":id") }}'.replace(':id', parentID),
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message || 'Parent has been deleted.', 'success')
                                .then(function() {
                                    loadParents();
                                });
                        } else {
                            Swal.fire('Error', response.message || 'Failed to delete parent', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON.message || 'Failed to delete parent', 'error');
                    }
                });
            }
        });
    });

    // Reset form when modal is closed
    $('#addParentModal').on('hidden.bs.modal', function() {
        $('#addParentForm')[0].reset();
        $('.text-danger').hide();
        $('.form-control').removeClass('is-invalid');
    });

    $('#editParentModal').on('hidden.bs.modal', function() {
        $('#editParentForm')[0].reset();
        $('.text-danger').hide();
        $('.form-control').removeClass('is-invalid');
        $('#current_photo_preview').html('');
    });
});
</script>

