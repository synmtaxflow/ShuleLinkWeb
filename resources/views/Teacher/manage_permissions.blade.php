@php
    $user_type = $user_type ?? session('user_type', 'Teacher');
    $permissionContext = $permissionContext ?? ($user_type === 'Staff' ? 'staff' : 'teacher');
    $permissionLabel = $permissionContext === 'staff' ? 'Staff' : 'Teacher';
    $permissionListRoute = $permissionContext === 'staff' ? 'staff.permissions' : 'teacher.permissions';
    $permissionStoreRoute = $permissionContext === 'staff' ? 'staff.permissions.store' : 'teacher.permissions.store';
@endphp

@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

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
            <strong>{{ $permissionLabel }} Permissions</strong>
        </div>
        <div class="card-body">
            <div class="form-loading" id="teacherTabLoading">
                <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> Loading...</span>
                <div class="form-progress"></div>
            </div>
            <ul class="nav nav-tabs permission-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'request' ? 'active' : '' }}" href="{{ route($permissionListRoute, ['tab' => 'request']) }}">
                        Request Permission
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'pending' ? 'active' : '' }}" href="{{ route($permissionListRoute, ['tab' => 'pending']) }}">
                        Pending Requests
                        @if($unreadPendingCount > 0)
                            <span class="badge-count">{{ $unreadPendingCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'approved' ? 'active' : '' }}" href="{{ route($permissionListRoute, ['tab' => 'approved']) }}">
                        Completed Requests
                        @if($unreadApprovedCount > 0)
                            <span class="badge-count">{{ $unreadApprovedCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'rejected' ? 'active' : '' }}" href="{{ route($permissionListRoute, ['tab' => 'rejected']) }}">
                        Rejected Requests
                        @if($unreadRejectedCount > 0)
                            <span class="badge-count">{{ $unreadRejectedCount }}</span>
                        @endif
                    </a>
                </li>
            </ul>

            @if($activeTab === 'request')
                <div class="section-title">Request Permission</div>
                <form method="POST" action="{{ route($permissionStoreRoute) }}" id="teacherPermissionForm">
                    @csrf
                    <div class="form-group mb-3">
                        <label>Time Mode</label>
                        <select class="form-select" name="time_mode" id="timeMode" required>
                            <option value="">-- Select --</option>
                            <option value="days">Days (Maximum 7 days)</option>
                            <option value="hours">Hours</option>
                        </select>
                    </div>

                    <div id="daysFields" style="display:none;">
                        <input type="hidden" name="days_count" id="teacherDaysCount" value="">
                        <div class="form-row">
                            <div class="col-md-6 mb-3">
                                <label>From Date</label>
                                <input type="date" class="form-control" name="start_date" id="teacherStartDate">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>To Date</label>
                                <input type="date" class="form-control" name="end_date" id="teacherEndDate">
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label>Total Days (max 7)</label>
                            <input type="text" class="form-control" id="teacherDaysDisplay" readonly>
                            <small class="text-danger d-none" id="teacherDaysError">Days exceed limit. Use 7 days or less.</small>
                        </div>
                    </div>

                    <div id="hoursFields" style="display:none;">
                        <div class="form-row">
                            <div class="col-md-6 mb-3">
                                <label>Start Time (24h)</label>
                                <input type="time" class="form-control" name="start_time">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>End Time (24h)</label>
                                <input type="time" class="form-control" name="end_time">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label>Reason Type</label>
                        <select class="form-select" name="reason_type" required>
                            <option value="medical">Medical</option>
                            <option value="official">Official</option>
                            <option value="professional">Professional</option>
                            <option value="emergency">Emergency</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>Reason Description</label>
                        <textarea class="form-control" name="reason_description" rows="3" required></textarea>
                    </div>

                    <button class="btn btn-primary-custom" type="submit" id="teacherPermissionSubmit">
                        <i class="fa fa-paper-plane"></i> Send Request
                    </button>
                </form>
            @endif

            @if($activeTab === 'pending')
                <div class="section-title">Pending Requests</div>
                @include('Teacher.partials.permission_table', ['permissions' => $pendingPermissions])
            @endif

            @if($activeTab === 'approved')
                <div class="section-title">Completed Requests</div>
                @include('Teacher.partials.permission_table', ['permissions' => $approvedPermissions])
            @endif

            @if($activeTab === 'rejected')
                <div class="section-title">Rejected Requests</div>
                @include('Teacher.partials.permission_table', ['permissions' => $rejectedPermissions])
            @endif
        </div>
    </div>
</div>

@include('includes.footer')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function initTeacherPermissionForm() {
        const timeMode = document.getElementById('timeMode');
        const daysFields = document.getElementById('daysFields');
        const hoursFields = document.getElementById('hoursFields');
        const startDateInput = document.getElementById('teacherStartDate');
        const endDateInput = document.getElementById('teacherEndDate');
        const daysDisplay = document.getElementById('teacherDaysDisplay');
        const daysCountInput = document.getElementById('teacherDaysCount');
        const daysError = document.getElementById('teacherDaysError');

        function calculateDays() {
            if (!startDateInput || !endDateInput || !daysDisplay || !daysCountInput || !daysError) return;
            const start = startDateInput.value ? new Date(startDateInput.value) : null;
            const end = endDateInput.value ? new Date(endDateInput.value) : null;
            if (!start || !end) {
                daysDisplay.value = '';
                daysCountInput.value = '';
                daysError.classList.add('d-none');
                return;
            }
            const diffMs = end.getTime() - start.getTime();
            const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24)) + 1;
            daysDisplay.value = diffDays > 0 ? diffDays : '';
            daysCountInput.value = diffDays > 0 ? diffDays : '';
            if (diffDays > 7) {
                daysError.classList.remove('d-none');
            } else {
                daysError.classList.add('d-none');
            }
        }

        function toggleTimeMode() {
            if (!timeMode) return;
            if (timeMode.value === 'days') {
                daysFields.style.display = 'block';
                hoursFields.style.display = 'none';
                calculateDays();
            } else if (timeMode.value === 'hours') {
                hoursFields.style.display = 'block';
                daysFields.style.display = 'none';
            } else {
                daysFields.style.display = 'none';
                hoursFields.style.display = 'none';
            }
        }

        if (timeMode) {
            timeMode.addEventListener('change', toggleTimeMode);
        }
        if (startDateInput) startDateInput.addEventListener('change', calculateDays);
        if (endDateInput) endDateInput.addEventListener('change', calculateDays);

        const permissionForm = document.getElementById('teacherPermissionForm');
        if (permissionForm) {
            permissionForm.addEventListener('submit', function(e) {
                if (timeMode && timeMode.value === 'days') {
                    calculateDays();
                    const days = parseInt(daysCountInput.value || '0', 10);
                    if (days > 7) {
                        e.preventDefault();
                        Swal.fire({ icon: 'error', title: 'Failed', text: 'Days exceed limit. Use 7 days or less.', confirmButtonColor: '#940000' });
                        return;
                    }
                }
                e.preventDefault();
                const submitBtn = document.getElementById('teacherPermissionSubmit');
                if (submitBtn) submitBtn.disabled = true;
                Swal.fire({ icon: 'info', title: 'Sending...', text: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                const formData = new FormData(permissionForm);
                fetch(permissionForm.action, {
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
                        permissionForm.reset();
                        if (daysFields) daysFields.style.display = 'none';
                        if (hoursFields) hoursFields.style.display = 'none';
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

    function loadTeacherTab(url) {
        const cardBody = document.querySelector('.card .card-body');
        if (!cardBody) return;
        const loadingBar = cardBody.querySelector('#teacherTabLoading');
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
                    bindTeacherTabLinks();
                    initTeacherPermissionForm();
                    const newLoadingBar = cardBody.querySelector('#teacherTabLoading');
                    if (newLoadingBar) newLoadingBar.style.display = 'none';
                }
            })
            .finally(() => {
                cardBody.style.opacity = '1';
                const currentLoadingBar = cardBody.querySelector('#teacherTabLoading');
                if (currentLoadingBar) currentLoadingBar.style.display = 'none';
            });
    }

    function bindTeacherTabLinks() {
        document.querySelectorAll('.permission-tabs .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                loadTeacherTab(this.href);
            });
        });
    }

    bindTeacherTabLinks();
    initTeacherPermissionForm();

    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Success', text: "{{ session('success') }}", confirmButtonColor: '#940000' });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Failed', text: "{{ session('error') }}", confirmButtonColor: '#940000' });
    @endif
</script>
