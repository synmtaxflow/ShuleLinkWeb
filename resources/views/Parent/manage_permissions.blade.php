@include('includes.parent_nav')

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    body, .content, .card, .btn, .form-control, .form-select, .table, .list-group-item, .alert {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .card, .alert, .btn, div, .form-control, .form-select {
        border-radius: 0 !important;
    }
    .bg-primary-custom { background-color: #940000 !important; }
    .btn-primary-custom { background-color: #940000; border-color: #940000; color: #fff; }
    .btn-primary-custom:hover { background-color: #b30000; border-color: #b30000; color: #fff; }
    .permission-tabs .nav-link { cursor: pointer; color: #940000; border-radius: 0; }
    .permission-tabs .nav-link.active { background: #fff5f5; color: #940000; font-weight: 600; border-color: #940000; }
    .section-title { font-weight: 600; margin-bottom: 12px; }
    .badge-count { background: #940000; color: #fff; border-radius: 10px; padding: 2px 6px; font-size: 0.75rem; margin-left: 6px; }
    .form-loading {
        display: none;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid rgba(148, 0, 0, 0.25);
        background: rgba(148, 0, 0, 0.05);
        margin-bottom: 12px;
    }
    .form-progress {
        position: relative;
        flex: 1;
        height: 8px;
        background: #f0f0f0;
        border-radius: 4px;
        overflow: hidden;
    }
    .form-progress::after {
        content: "";
        position: absolute;
        left: -40%;
        width: 40%;
        height: 100%;
        background: #940000;
        animation: progressSlide 1.1s linear infinite;
    }
    @keyframes progressSlide {
        0% { left: -40%; }
        100% { left: 100%; }
    }
</style>

<div class="breadcrumbs">
    <div class="col-sm-6">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Permission Requests</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header bg-primary-custom text-white">
            <strong>Student Permissions</strong>
        </div>
        <div class="card-body">
            <div class="form-loading" id="parentTabLoading">
                <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> Loading...</span>
                <div class="form-progress"></div>
            </div>
            <ul class="nav nav-tabs permission-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'request' ? 'active' : '' }}" href="{{ route('parent.permissions', ['tab' => 'request']) }}">
                        Request Permission
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'pending' ? 'active' : '' }}" href="{{ route('parent.permissions', ['tab' => 'pending']) }}">
                        Pending Requests
                        @if($unreadPendingCount > 0)
                            <span class="badge-count">{{ $unreadPendingCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'approved' ? 'active' : '' }}" href="{{ route('parent.permissions', ['tab' => 'approved']) }}">
                        Completed Requests
                        @if($unreadApprovedCount > 0)
                            <span class="badge-count">{{ $unreadApprovedCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'rejected' ? 'active' : '' }}" href="{{ route('parent.permissions', ['tab' => 'rejected']) }}">
                        Rejected Requests
                        @if($unreadRejectedCount > 0)
                            <span class="badge-count">{{ $unreadRejectedCount }}</span>
                        @endif
                    </a>
                </li>
            </ul>

            @if($activeTab === 'request')
                <div class="section-title">Request Permission</div>
                <form method="POST" action="{{ route('parent.permissions.store') }}" enctype="multipart/form-data" id="parentPermissionForm">
                    @csrf
                    <div class="form-group mb-3">
                        <label>Student</label>
                        <select class="form-select" name="student_id" required>
                            <option value="">-- Select Student --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->studentID }}">
                                    {{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="days_count" id="parentDaysCount" value="">
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label>From Date</label>
                            <input type="date" class="form-control" name="start_date" id="parentStartDate" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>To Date</label>
                            <input type="date" class="form-control" name="end_date" id="parentEndDate" required>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label>Total Days (max 7)</label>
                        <input type="text" class="form-control" id="parentDaysDisplay" readonly>
                        <small class="text-danger d-none" id="parentDaysError">Days exceed limit. Use 7 days or less.</small>
                    </div>
                    <div class="form-group mb-3">
                        <label>Reason Type</label>
                        <select class="form-select" name="reason_type" id="parentReasonType" required>
                            <option value="medical">Medical</option>
                            <option value="official">Official</option>
                            <option value="professional">Professional</option>
                            <option value="emergency">Emergency</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group mb-3" id="medicalAttachmentField" style="display:none;">
                        <label>Medical Attachment (PDF/Image)</label>
                        <input type="file" class="form-control" name="attachment" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <div class="form-group mb-3">
                        <label>Description</label>
                        <textarea class="form-control" name="reason_description" rows="3" required></textarea>
                    </div>

                    <button class="btn btn-primary-custom" type="submit" id="parentPermissionSubmit">
                        <i class="fa fa-paper-plane"></i> Send Request
                    </button>
                </form>
            @endif

            @if($activeTab === 'pending')
                <div class="section-title">Pending Requests</div>
                @include('Parent.partials.permission_table', ['permissions' => $pendingPermissions, 'studentNames' => $studentNames])
            @endif

            @if($activeTab === 'approved')
                <div class="section-title">Completed Requests</div>
                @include('Parent.partials.permission_table', ['permissions' => $approvedPermissions, 'studentNames' => $studentNames])
            @endif

            @if($activeTab === 'rejected')
                <div class="section-title">Rejected Requests</div>
                @include('Parent.partials.permission_table', ['permissions' => $rejectedPermissions, 'studentNames' => $studentNames])
            @endif
        </div>
    </div>
</div>

@include('includes.footer')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function initParentPermissionForm() {
        const reasonSelect = document.getElementById('parentReasonType');
        const medicalField = document.getElementById('medicalAttachmentField');
        const medicalInput = medicalField ? medicalField.querySelector('input[type="file"]') : null;
        const parentStartDate = document.getElementById('parentStartDate');
        const parentEndDate = document.getElementById('parentEndDate');
        const parentDaysDisplay = document.getElementById('parentDaysDisplay');
        const parentDaysCount = document.getElementById('parentDaysCount');
        const parentDaysError = document.getElementById('parentDaysError');
        if (reasonSelect) {
            const toggleMedical = () => {
                if (reasonSelect.value === 'medical') {
                    if (medicalField) medicalField.style.display = 'block';
                    if (medicalInput) medicalInput.required = true;
                } else {
                    if (medicalField) medicalField.style.display = 'none';
                    if (medicalInput) medicalInput.required = false;
                }
            };
            reasonSelect.addEventListener('change', toggleMedical);
            toggleMedical();
        }

        function calculateParentDays() {
            if (!parentStartDate || !parentEndDate || !parentDaysDisplay || !parentDaysCount || !parentDaysError) return;
            const start = parentStartDate.value ? new Date(parentStartDate.value) : null;
            const end = parentEndDate.value ? new Date(parentEndDate.value) : null;
            if (!start || !end) {
                parentDaysDisplay.value = '';
                parentDaysCount.value = '';
                parentDaysError.classList.add('d-none');
                return;
            }
            const diffMs = end.getTime() - start.getTime();
            const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24)) + 1;
            parentDaysDisplay.value = diffDays > 0 ? diffDays : '';
            parentDaysCount.value = diffDays > 0 ? diffDays : '';
            if (diffDays > 7) {
                parentDaysError.classList.remove('d-none');
            } else {
                parentDaysError.classList.add('d-none');
            }
        }

        if (parentStartDate) parentStartDate.addEventListener('change', calculateParentDays);
        if (parentEndDate) parentEndDate.addEventListener('change', calculateParentDays);

        const parentForm = document.getElementById('parentPermissionForm');
        if (parentForm) {
            parentForm.addEventListener('submit', function(e) {
                calculateParentDays();
                const days = parseInt(parentDaysCount.value || '0', 10);
                if (days > 7) {
                    e.preventDefault();
                    Swal.fire({ icon: 'error', title: 'Failed', text: 'Days exceed limit. Use 7 days or less.', confirmButtonColor: '#940000' });
                    return;
                }
                e.preventDefault();
                const submitBtn = document.getElementById('parentPermissionSubmit');
                if (submitBtn) submitBtn.disabled = true;
                Swal.fire({ icon: 'info', title: 'Sending...', text: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                const formData = new FormData(parentForm);
                fetch(parentForm.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Success', text: data.message, confirmButtonColor: '#940000' });
                        parentForm.reset();
                    if (medicalField) medicalField.style.display = 'none';
                    if (medicalInput) medicalInput.required = false;
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: data.message || 'Failed to send request.', confirmButtonColor: '#940000' });
                    }
                })
                .catch(() => {
                    Swal.fire({ icon: 'error', title: 'Failed', text: 'Failed to send request.', confirmButtonColor: '#940000' });
                })
                .finally(() => {
                    if (submitBtn) submitBtn.disabled = false;
                });
            });
        }
    }

    function loadParentTab(url) {
        const cardBody = document.querySelector('.card .card-body');
        if (!cardBody) return;
        const loadingBar = cardBody.querySelector('#parentTabLoading');
        if (loadingBar) loadingBar.style.display = 'flex';
        cardBody.style.opacity = '0.6';
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newBody = doc.querySelector('.card .card-body');
                if (newBody) {
                    cardBody.innerHTML = newBody.innerHTML;
                    bindParentTabLinks();
                    initParentPermissionForm();
                    const newLoadingBar = cardBody.querySelector('#parentTabLoading');
                    if (newLoadingBar) newLoadingBar.style.display = 'none';
                }
            })
            .finally(() => {
                cardBody.style.opacity = '1';
                const currentLoadingBar = cardBody.querySelector('#parentTabLoading');
                if (currentLoadingBar) currentLoadingBar.style.display = 'none';
            });
    }

    function bindParentTabLinks() {
        document.querySelectorAll('.permission-tabs .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                loadParentTab(this.href);
            });
        });
    }

    bindParentTabLinks();
    initParentPermissionForm();

    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Success', text: "{{ session('success') }}", confirmButtonColor: '#940000' });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Failed', text: "{{ session('error') }}", confirmButtonColor: '#940000' });
    @endif
</script>
