@php
    $userType = $user_type ?? Session::get('user_type');
@endphp

@if($userType == 'Admin')
    @include('includes.Admin_nav')
@elseif($userType == 'Teacher')
    @include('includes.teacher_nav')
@else
    @include('includes.staff_nav')
@endif

<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-0" style="color: #940000; font-weight: 700;">Departments Management</h4>
                            <p class="text-muted small mb-0">Manage school academic and administrative departments</p>
                        </div>
                        @if($userType == 'Admin')
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-success btn-sm" data-toggle="modal" data-target="#smsHodModal">
                                <i class="fa fa-envelope"></i> SMS to HODs
                            </button>
                            <button class="btn btn-primary-custom btn-sm" data-toggle="modal" data-target="#addDeptModal">
                                <i class="fa fa-plus"></i> Add Department
                            </button>
                        </div>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success border-0 shadow-sm" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        @if($userType == 'Admin')
                                        <th style="width: 30px;" class="text-center">
                                            <input type="checkbox" id="checkAllDepts" style="cursor: pointer;">
                                        </th>
                                        @endif
                                        <th>#</th>
                                        <th>Department Name</th>
                                        <th>Type</th>
                                        <th>Head of Department</th>
                                        <th>Members</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($departments as $dept)
                                        <tr>
                                            @if($userType == 'Admin')
                                            <td class="text-center">
                                                <input type="checkbox" class="dept-checkbox" 
                                                       id="dept_{{ $dept->departmentID }}" 
                                                       value="{{ $dept->departmentID }}"
                                                       style="cursor: pointer;">
                                            </td>
                                            @endif
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="fw-bold">
                                                {{ $dept->department_name }}
                                                @php
                                                    $curT = Session::get('teacherID');
                                                    $curS = Session::get('staffID');
                                                    $isDeptHOD = ($dept->head_teacherID == $curT && $curT) || ($dept->head_staffID == $curS && $curS);
                                                @endphp
                                                @if($isDeptHOD)
                                                    <br><span class="badge badge-success" style="font-size: 0.7rem;"><i class="fa fa-star"></i> You are HOD</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $dept->type == 'Academic' ? 'bg-info' : 'bg-secondary' }}">
                                                    {{ $dept->type }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($dept->type == 'Academic')
                                                    {{ $dept->headTeacher->first_name ?? 'Not Assigned' }} {{ $dept->headTeacher->last_name ?? '' }}
                                                @else
                                                    {{ $dept->headStaff->first_name ?? 'Not Assigned' }} {{ $dept->headStaff->last_name ?? '' }}
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary rounded-pill">{{ $dept->members->count() }}</span>
                                                <button class="btn btn-sm btn-link p-0 ms-1 view-members" data-id="{{ $dept->departmentID }}" data-name="{{ $dept->department_name }}">
                                                    {{ $user_type == 'Admin' ? 'Manage' : 'View' }}
                                                </button>
                                            </td>
                                            <td>
                                                @if($user_type == 'Admin')
                                                <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editDeptModal{{ $dept->departmentID }}">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <form action="{{ route('sgpm.departments.destroy', $dept->departmentID) }}" method="POST" class="d-inline ajax-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger confirm-delete">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                                @else
                                                <span class="text-muted small">No Actions</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No departments found. Create one to get started.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modals Loop (Outside Table for fixed backdrop) -->
@foreach($departments as $dept)
    <div class="modal fade" id="editDeptModal{{ $dept->departmentID }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0" style="border-radius: 15px;">
                <form action="{{ route('sgpm.departments.update', $dept->departmentID) }}" method="POST" class="ajax-form">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Department</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Department Name</label>
                            <input type="text" name="department_name" class="form-control" value="{{ $dept->department_name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select edit-dept-type" required>
                                <option value="Academic" {{ $dept->type == 'Academic' ? 'selected' : '' }}>Academic</option>
                                <option value="Administrative" {{ $dept->type == 'Administrative' ? 'selected' : '' }}>Administrative</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Head of Department</label>
                            <select name="head_id" class="form-select edit-head-select">
                                <option value="">-- Select Head --</option>
                                @php
                                    $currentHeadID = $dept->head_teacherID ? 't_'.$dept->head_teacherID : ($dept->head_staffID ? 's_'.$dept->head_staffID : '');
                                    $eligibleHeads = ($dept->type == 'Academic') ? $teachers->map(fn($t) => ['id' => 't_'.$t->id, 'name' => $t->first_name.' '.$t->last_name]) : 
                                        $staff->map(fn($s) => ['id' => 's_'.$s->id, 'name' => $s->first_name.' '.$s->last_name.' (Staff)'])->concat(
                                        $teachers->map(fn($t) => ['id' => 't_'.$t->id, 'name' => $t->first_name.' '.$t->last_name.' (Teacher)']));
                                @endphp
                                @foreach($eligibleHeads as $head)
                                    <option value="{{ $head['id'] }}" {{ $currentHeadID == $head['id'] ? 'selected' : '' }}>{{ $head['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-custom">Update Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- SMS to HODs Modal (Admin Only) -->
@if($user_type == 'Admin')
<div class="modal fade" id="smsHodModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow" style="border-radius: 15px;">
            <div class="modal-header">
                <h5 class="modal-title text-success"><i class="fa fa-envelope"></i> SMS to HODs</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2" style="font-size: 0.85rem;">
                    <i class="fa fa-info-circle"></i> Ujumbe utatumwa kwa Marais/Wakuu wa idara zilizochaguliwa. 
                    Ili kutuma kwa WOTE, usi-tick kibox chochote kwenye table.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Ujumbe</label>
                    <textarea id="hod_sms_message" class="form-control" rows="4" placeholder="Andika ujumbe kwa HODs..." required></textarea>
                </div>
                <button type="button" class="btn btn-success w-100" id="sendSmsHodBtn">
                    <i class="fa fa-paper-plane"></i> Send SMS to HODs
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Members Management Modal -->
<div class="modal fade" id="membersModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0" id="membersModalContent" style="border-radius: 15px;">
            <div class="text-center py-5">
                <i class="fa fa-spinner fa-spin fa-2x"></i>
                <p>Loading members...</p>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addDeptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0" style="border-radius: 15px;">
            <form action="{{ route('sgpm.departments.store') }}" method="POST" class="ajax-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Department</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Department Name</label>
                        <input type="text" name="department_name" class="form-control" placeholder="e.g., Mathematics, Finance" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" id="dept_type" required>
                            <option value="Academic">Academic</option>
                            <option value="Administrative">Administrative</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Head of Department</label>
                        <select name="head_id" class="form-select" id="head_select">
                            <option value="">-- Select Head --</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Save Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const teachers = @json($teachers);
    const staff = @json($staff);

    function populateHeads(type, selectElement) {
        selectElement.innerHTML = '<option value="">-- Select Head --</option>';
        if (type === 'Academic') {
            teachers.forEach(item => {
                selectElement.innerHTML += `<option value="t_${item.id}">${item.first_name} ${item.last_name}</option>`;
            });
        } else {
            // Administrative brings both staff and teachers
            staff.forEach(item => {
                selectElement.innerHTML += `<option value="s_${item.id}">${item.first_name} ${item.last_name} (Staff)</option>`;
            });
            teachers.forEach(item => {
                selectElement.innerHTML += `<option value="t_${item.id}">${item.first_name} ${item.last_name} (Teacher)</option>`;
            });
        }
    }

    // Add Dept Modal Listener
    const deptTypeSelect = document.getElementById('dept_type');
    const headSelect = document.getElementById('head_select');
    if(deptTypeSelect) {
        deptTypeSelect.addEventListener('change', function() {
            populateHeads(this.value, headSelect);
        });
        // Initial trigger
        populateHeads(deptTypeSelect.value, headSelect);
    }

    // Edit Modal Listeners (Dynamic)
    $(document).on('change', '.edit-dept-type', function() {
        const $modal = $(this).closest('.modal');
        const $headSelect = $modal.find('.edit-head-select');
        populateHeads(this.value, $headSelect[0]);
    });

    // Fix for the backdrop issue: ensure modals are not nested
    $(document).on('show.bs.modal', '.modal', function() {
        $(this).appendTo('body');
    });

    // Members Management AJAX
    $(document).on('click', '.view-members', function() {
        const deptId = $(this).data('id');
        $('#membersModal').modal('show');
        loadMembers(deptId);
    });

    function loadMembers(deptId) {
        $('#membersModalContent').html('<div class="text-center py-5"><i class="fa fa-spinner fa-spin fa-3x text-primary"></i><br><p class="mt-2">Loading members...</p></div>');
        $.get(`/sgpm/departments/${deptId}/members`, function(response) {
            if (response.success) {
                $('#membersModalContent').html(response.html);
            }
        }).fail(function() {
            $('#membersModalContent').html('<div class="alert alert-danger mx-3">Failed to load members.</div>');
        });
    }

    // AJAX handle add member
    $(document).on('submit', '#addMemberForm', function(e) {
        e.preventDefault();
        const activeDeptId = window.currentDeptId;
        const $form = $(this);
        const $btn = $form.find('button[type="submit"]');
        
        // Collect all selected member IDs
        const selectedMembers = [];
        $('.member-select').each(function() {
            if ($(this).val()) selectedMembers.push($(this).val());
        });

        if (selectedMembers.length === 0) return;

        // Show loading state
        const originalBtnHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Assigning...');

        $.post(`/sgpm/departments/${activeDeptId}/members`, {
            _token: '{{ csrf_token() }}',
            members: selectedMembers
        }, function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success', 
                    title: 'Assigned!', 
                    text: response.message, 
                    timer: 1500, 
                    showConfirmButton: false
                });
                loadMembers(activeDeptId);
            } else {
                Swal.fire('Error', response.message || 'Something went wrong', 'error');
                $btn.prop('disabled', false).html(originalBtnHtml);
            }
        }).fail(function() {
            Swal.fire('Error', 'Server error occurred', 'error');
            $btn.prop('disabled', false).html(originalBtnHtml);
        });
    });

    $(document).on('click', '.remove-member', function() {
        const memberId = $(this).data('id');
        const activeDeptId = window.currentDeptId;
        
        Swal.fire({
            title: 'Remove member?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, remove'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/sgpm/departments/members/${memberId}`,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            loadMembers(activeDeptId);
                        }
                    }
                });
            }
        });
    });

    $(document).on('click', '.view-members', function() {
        window.currentDeptId = $(this).data('id');
    });

    // Group SMS Handler
    $(document).on('submit', '#groupSmsForm', function(e) {
        e.preventDefault();
        const activeDeptId = window.currentDeptId;
        const $btn = $('#sendSmsBtn');
        const message = $('#sms_message').val();
        
        // Collect checked member IDs
        const selectedMemberIds = [];
        $('.member-checkbox:checked').each(function() {
            selectedMemberIds.push($(this).val());
        });

        if (selectedMemberIds.length === 0) {
            Swal.fire('Selection Required', 'Please select at least one member to send SMS.', 'warning');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');

        const url = "{{ route('sgpm.departments.members.sms', ':id') }}".replace(':id', activeDeptId);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                message: message,
                member_ids: selectedMemberIds
            },
            timeout: 60000, // 60 seconds timeout
            success: function(response) {
                if (response.success) {
                    Swal.fire('Sent!', response.message, 'success');
                    $('#sms_message').val('');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send SMS');
            },
            error: function(xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Server error occurred';
                Swal.fire('Error', msg, 'error');
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send SMS');
            }
        });
    });

    // Admin SMS to HODs Handler
    $(document).on('change', '#checkAllDepts', function() {
        $('.dept-checkbox').prop('checked', $(this).prop('checked'));
    });

    $(document).on('click', '#sendSmsHodBtn', function() {
        const message = $('#hod_sms_message').val();
        if (!message) {
            Swal.fire('Required', 'Tafadhali andika ujumbe kwanza.', 'warning');
            return;
        }

        const selectedDepts = [];
        $('.dept-checkbox:checked').each(function() {
            selectedDepts.push($(this).val());
        });

        const $btn = $(this);
        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');

        $.ajax({
            url: `{{ route('sgpm.departments.hods.sms') }}`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                message: message,
                department_ids: selectedDepts.length > 0 ? selectedDepts : null
            },
            timeout: 60000,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Sent!', response.message, 'success');
                    $('#smsHodModal').modal('hide');
                    $('#hod_sms_message').val('');
                    $('.dept-checkbox, #checkAllDepts').prop('checked', false);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
                $btn.prop('disabled', false).html(originalHtml);
            },
            error: function(xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Server error';
                Swal.fire('Error', msg, 'error');
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    });
</script>

@include('includes.footer')
