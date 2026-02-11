@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
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
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<div class="container-fluid mt-4">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light rounded">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0 text-primary-custom">
                    <i class="bi bi-handshake-fill"></i> Manage Sponsors
                </h4>
                <button class="btn btn-primary-custom" id="addSponsorBtn" data-bs-toggle="modal" data-bs-target="#addSponsorModal">
                    <i class="bi bi-plus-circle"></i> Register New Sponsor
                </button>
            </div>
        </div>
    </div>

    <!-- Sponsors Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <table id="sponsorsTable" class="table table-striped table-hover">
                <thead class="bg-primary-custom text-white">
                    <tr>
                        <th>#</th>
                        <th>Sponsor Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sponsors as $index => $sponsor)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $sponsor->sponsor_name }}</td>
                        <td>{{ $sponsor->contact_person ?? '-' }}</td>
                        <td>{{ $sponsor->phone ?? '-' }}</td>
                        <td>{{ $sponsor->email ?? '-' }}</td>
                        <td>
                            @if($sponsor->status == 'Active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning edit-sponsor-btn" 
                                data-id="{{ $sponsor->sponsorID }}"
                                data-name="{{ $sponsor->sponsor_name }}"
                                data-description="{{ $sponsor->description }}"
                                data-contact="{{ $sponsor->contact_person }}"
                                data-phone="{{ $sponsor->phone }}"
                                data-email="{{ $sponsor->email }}"
                                data-status="{{ $sponsor->status }}">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-sponsor-btn" data-id="{{ $sponsor->sponsorID }}" data-name="{{ $sponsor->sponsor_name }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Sponsor Modal -->
<div class="modal fade" id="addSponsorModal" tabindex="-1" aria-labelledby="addSponsorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addSponsorModalLabel">
                    <i class="bi bi-plus-circle"></i> Register New Sponsor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addSponsorForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="sponsor_name" class="form-label">Sponsor Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sponsor_name" name="sponsor_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_person" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <div class="input-group">
                                <span class="input-group-text">255</span>
                                <input type="text" class="form-control" id="phone" name="phone" placeholder="7XXXXXXXX" pattern="[0-9]{9}" maxlength="9">
                            </div>
                            <small class="text-muted">Format: 7XXXXXXXX (9 digits)</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-check-circle"></i> Register Sponsor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Sponsor Modal -->
<div class="modal fade" id="editSponsorModal" tabindex="-1" aria-labelledby="editSponsorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editSponsorModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Sponsor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSponsorForm">
                @csrf
                <input type="hidden" id="edit_sponsor_id" name="sponsor_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_sponsor_name" class="form-label">Sponsor Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_sponsor_name" name="sponsor_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_contact_person" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="edit_contact_person" name="contact_person">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_phone" class="form-label">Phone</label>
                            <div class="input-group">
                                <span class="input-group-text">255</span>
                                <input type="text" class="form-control" id="edit_phone" name="phone" placeholder="7XXXXXXXX" pattern="[0-9]{9}" maxlength="9">
                            </div>
                            <small class="text-muted">Format: 7XXXXXXXX (9 digits)</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle"></i> Update Sponsor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@include('includes.footer')

<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#sponsorsTable').DataTable({
            order: [[1, 'asc']],
            pageLength: 25
        });

        // Add Sponsor Form Submit
        $('#addSponsorForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: '{{ route("sponsors.store") }}',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to register sponsor';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        confirmButtonColor: '#940000'
                    });
                }
            });
        });

        // Edit Sponsor Button Click
        $(document).on('click', '.edit-sponsor-btn', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const description = $(this).data('description');
            const contact = $(this).data('contact');
            let phone = $(this).data('phone') || '';
            const email = $(this).data('email');
            const status = $(this).data('status');

            // Strip 255 prefix if present (since the input field already has 255 prefix)
            if (phone && phone.toString().startsWith('255')) {
                phone = phone.toString().substring(3);
            }

            $('#edit_sponsor_id').val(id);
            $('#edit_sponsor_name').val(name);
            $('#edit_description').val(description);
            $('#edit_contact_person').val(contact);
            $('#edit_phone').val(phone);
            $('#edit_email').val(email);
            $('#edit_status').val(status);

            // Use Bootstrap 5 modal API
            var editModal = new bootstrap.Modal(document.getElementById('editSponsorModal'));
            editModal.show();
        });

        // Edit Sponsor Form Submit
        $('#editSponsorForm').on('submit', function(e) {
            e.preventDefault();
            const sponsorId = $('#edit_sponsor_id').val();
            
            $.ajax({
                url: '{{ url("sponsors/update") }}/' + sponsorId,
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to update sponsor';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        confirmButtonColor: '#940000'
                    });
                }
            });
        });

        // Delete Sponsor Button Click
        $(document).on('click', '.delete-sponsor-btn', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            Swal.fire({
                title: 'Are you sure?',
                text: `Delete sponsor "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ url("sponsors/delete") }}/' + id,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    confirmButtonColor: '#940000'
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Failed to delete sponsor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMessage,
                                confirmButtonColor: '#940000'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
