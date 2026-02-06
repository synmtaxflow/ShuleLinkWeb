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
    .card, .alert, .btn, div, .form-control, .form-select { border-radius: 0 !important; }
    .bg-primary-custom { background-color: #940000 !important; }
    .btn-primary-custom { background-color: #940000; border-color: #940000; color: #fff; }
    .btn-primary-custom:hover { background-color: #b30000; border-color: #b30000; color: #fff; }
    .permission-tabs .nav-link { cursor: pointer; color: #940000; border-radius: 0; }
    .permission-tabs .nav-link.active { background: #fff5f5; color: #940000; font-weight: 600; border-color: #940000; }
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
                <h1>Permissions</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header bg-primary-custom text-white">
            <strong>Permission Requests</strong>
        </div>
        <div class="card-body">
            <div class="form-loading" id="permissionLoading">
                <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> Loading...</span>
                <div class="form-progress"></div>
            </div>
            <ul class="nav nav-tabs permission-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'teacher' ? 'active' : '' }}" href="{{ route('admin.hr.permission', ['tab' => 'teacher']) }}">
                        Teacher Permission
                        @if(!empty($tabCounts['teacher']))
                            <span class="badge-count">{{ $tabCounts['teacher'] }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'staff' ? 'active' : '' }}" href="{{ route('admin.hr.permission', ['tab' => 'staff']) }}">
                        Staff Permission
                        @if(!empty($tabCounts['staff']))
                            <span class="badge-count">{{ $tabCounts['staff'] }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'student' ? 'active' : '' }}" href="{{ route('admin.hr.permission', ['tab' => 'student']) }}">
                        Student Permission
                        @if(!empty($tabCounts['student']))
                            <span class="badge-count">{{ $tabCounts['student'] }}</span>
                        @endif
                    </a>
                </li>
            </ul>

            <div class="row mb-3">
                <div class="col-md-3">
                    <input type="date" class="form-control" name="date_from" value="{{ $dateFrom }}" form="permissionFilterForm" id="filterDateFrom">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="date_to" value="{{ $dateTo }}" form="permissionFilterForm" id="filterDateTo">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Search name or ID" value="{{ $search }}" form="permissionFilterForm" id="filterSearch">
                </div>
                <div class="col-md-2">
                    <form id="permissionFilterForm" method="GET" action="{{ route('admin.hr.permission') }}">
                        <input type="hidden" name="tab" value="{{ $activeTab }}">
                        <button class="btn btn-primary-custom w-100" type="submit"><i class="fa fa-search"></i> Filter</button>
                    </form>
                </div>
            </div>

            <div class="mb-3">
                <span class="badge badge-warning">Pending: {{ $pendingCount }}</span>
                <span class="badge badge-success ml-2">Approved: {{ $approvedCount }}</span>
                <span class="badge badge-danger ml-2">Rejected: {{ $rejectedCount }}</span>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-primary-custom text-white">
                        <tr>
                            <th>Requester</th>
                            <th>Mode</th>
                            <th>Period</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Attachment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="permissionTableBody">
                        @forelse($permissions as $permission)
                            <tr data-permission-id="{{ $permission->permissionID }}">
                                <td>
                                    @if($activeTab === 'student')
                                        {{ $permission->student ? trim(($permission->student->first_name ?? '') . ' ' . ($permission->student->last_name ?? '')) : 'N/A' }}
                                    @elseif($activeTab === 'staff')
                                        {{ $permission->staff ? trim(($permission->staff->first_name ?? '') . ' ' . ($permission->staff->last_name ?? '')) : 'N/A' }}
                                    @else
                                        {{ $permission->teacher ? trim(($permission->teacher->first_name ?? '') . ' ' . ($permission->teacher->last_name ?? '')) : 'N/A' }}
                                    @endif
                                </td>
                                <td>{{ ucfirst($permission->time_mode) }}</td>
                                <td>
                                    @if($permission->time_mode === 'days')
                                        {{ $permission->start_date }} - {{ $permission->end_date }} ({{ $permission->days_count }} days)
                                    @else
                                        {{ $permission->start_time }} - {{ $permission->end_time }}
                                    @endif
                                </td>
                                <td>{{ ucfirst($permission->reason_type) }}</td>
                                <td class="permission-status">
                                    @if($permission->status === 'approved')
                                        <span class="badge badge-success">Approved</span>
                                    @elseif($permission->status === 'rejected')
                                        <span class="badge badge-danger">Rejected</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if($permission->attachment_path)
                                        <a href="{{ route('admin.permissions.attachment', $permission->permissionID) }}" target="_blank">View</a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="permission-actions">
                                    @if($permission->status === 'pending')
                                        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#approveModal{{ $permission->permissionID }}">
                                            Approve
                                        </button>
                                        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal{{ $permission->permissionID }}">
                                            Reject
                                        </button>
                                    @else
                                        <span class="text-muted">Reviewed</span>
                                    @endif
                                </td>
                            </tr>

                            <div class="modal fade" id="approveModal{{ $permission->permissionID }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Approve Permission</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <form method="POST" action="{{ route('admin.permissions.approve') }}" enctype="multipart/form-data" class="permission-approve-form" data-permission-id="{{ $permission->permissionID }}">
                                            @csrf
                                            <input type="hidden" name="permissionID" value="{{ $permission->permissionID }}">
                                            <div class="modal-body">
                                                <div class="form-group mb-3">
                                                    <label>Response</label>
                                                    <textarea class="form-control" name="admin_response" rows="3" required></textarea>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label>Upload Attachment (optional)</label>
                                                    <input type="file" class="form-control" name="admin_attachment" accept=".pdf,.jpg,.jpeg,.png">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary-custom">Approve</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="rejectModal{{ $permission->permissionID }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reject Permission</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <form method="POST" action="{{ route('admin.permissions.reject') }}" class="permission-reject-form" data-permission-id="{{ $permission->permissionID }}">
                                            @csrf
                                            <input type="hidden" name="permissionID" value="{{ $permission->permissionID }}">
                                            <div class="modal-body">
                                                <div class="form-group mb-3">
                                                    <label>Response</label>
                                                    <textarea class="form-control" name="admin_response" rows="3" required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-danger">Reject</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No permission requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="permissionModals"></div>
        </div>
    </div>
</div>

@include('includes.footer')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Success', text: "{{ session('success') }}", confirmButtonColor: '#940000' });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Failed', text: "{{ session('error') }}", confirmButtonColor: '#940000' });
    @endif

    const filterForm = document.getElementById('permissionFilterForm');
    const loadingBar = document.getElementById('permissionLoading');
    const tableBody = document.getElementById('permissionTableBody');
    const activeTab = "{{ $activeTab }}";

    function renderPermissionRows(items) {
        const modalContainer = document.getElementById('permissionModals');
        if (!items || items.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center">No permission requests found.</td></tr>';
            if (modalContainer) modalContainer.innerHTML = '';
            return;
        }
        let modalsHtml = '';
        tableBody.innerHTML = items.map(item => {
            const period = item.timeMode === 'days'
                ? `${item.startDate || ''} - ${item.endDate || ''} (${item.daysCount || 0} days)`
                : `${item.startTime || ''} - ${item.endTime || ''}`;
            const statusBadge = item.status === 'approved'
                ? '<span class="badge badge-success">Approved</span>'
                : item.status === 'rejected'
                    ? '<span class="badge badge-danger">Rejected</span>'
                    : '<span class="badge badge-warning">Pending</span>';
            const attachmentLink = item.attachment
                ? `<a href="${item.attachment}" target="_blank">View</a>`
                : 'N/A';
            let actions = '<span class="text-muted">Reviewed</span>';
            if (item.status === 'pending') {
                actions = `
                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#approveModal${item.permissionID}">
                        Approve
                    </button>
                    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal${item.permissionID}">
                        Reject
                    </button>
                `;
                modalsHtml += `
                    <div class="modal fade" id="approveModal${item.permissionID}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Approve Permission</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <form method="POST" action="{{ route('admin.permissions.approve') }}" enctype="multipart/form-data" class="permission-approve-form" data-permission-id="${item.permissionID}">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="permissionID" value="${item.permissionID}">
                                    <div class="modal-body">
                                        <div class="form-group mb-3">
                                            <label>Response</label>
                                            <textarea class="form-control" name="admin_response" rows="3" required></textarea>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label>Upload Attachment (optional)</label>
                                            <input type="file" class="form-control" name="admin_attachment" accept=".pdf,.jpg,.jpeg,.png">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary-custom">Approve</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="rejectModal${item.permissionID}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Reject Permission</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <form method="POST" action="{{ route('admin.permissions.reject') }}" class="permission-reject-form" data-permission-id="${item.permissionID}">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="permissionID" value="${item.permissionID}">
                                    <div class="modal-body">
                                        <div class="form-group mb-3">
                                            <label>Response</label>
                                            <textarea class="form-control" name="admin_response" rows="3" required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-danger">Reject</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                `;
            }
            return `
                <tr data-permission-id="${item.permissionID}">
                    <td>${item.requesterName || 'N/A'}</td>
                    <td>${item.timeMode ? item.timeMode.charAt(0).toUpperCase() + item.timeMode.slice(1) : ''}</td>
                    <td>${period}</td>
                    <td>${item.reasonType ? item.reasonType.charAt(0).toUpperCase() + item.reasonType.slice(1) : ''}</td>
                    <td class="permission-status">${statusBadge}</td>
                    <td>${attachmentLink}</td>
                    <td class="permission-actions">${actions}</td>
                </tr>
            `;
        }).join('');
        if (modalContainer) modalContainer.innerHTML = modalsHtml;
    }

    function submitAdminFilters() {
        if (!filterForm) return;
        if (loadingBar) loadingBar.style.display = 'flex';
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        fetch(`${filterForm.action}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderPermissionRows(data.data);
            } else {
                renderPermissionRows([]);
            }
        })
        .catch(() => renderPermissionRows([]))
        .finally(() => {
            if (loadingBar) loadingBar.style.display = 'none';
        });
    }

    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitAdminFilters();
        });
    }

    function setActiveTab(link) {
        document.querySelectorAll('.permission-tabs .nav-link').forEach(tab => {
            tab.classList.remove('active');
        });
        link.classList.add('active');
    }

    function bindAdminTabLinks() {
        document.querySelectorAll('.permission-tabs .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const tabInput = document.querySelector('#permissionFilterForm input[name="tab"]');
                if (tabInput) {
                    const url = new URL(this.href, window.location.origin);
                    const tabValue = url.searchParams.get('tab') || 'teacher';
                    tabInput.value = tabValue;
                }
                setActiveTab(this);
                submitAdminFilters();
            });
        });
    }

    bindAdminTabLinks();

    document.addEventListener('submit', function(e) {
        const approveForm = e.target.closest('.permission-approve-form');
        const rejectForm = e.target.closest('.permission-reject-form');
        if (!approveForm && !rejectForm) {
            return;
        }
        e.preventDefault();
        const form = approveForm || rejectForm;
        const permissionId = form.getAttribute('data-permission-id');
        const formData = new FormData(form);
        Swal.fire({ icon: 'info', title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(async res => {
            let data = null;
            try {
                data = await res.json();
            } catch (err) {
                data = null;
            }
            if (!res.ok) {
                const message = data && data.message ? data.message : `Operation failed (${res.status}).`;
                throw new Error(message);
            }
            return data;
        })
        .then(data => {
            if (data && data.success) {
                Swal.fire({ icon: 'success', title: 'Success', text: data.message, confirmButtonColor: '#940000' });
                const row = document.querySelector(`tr[data-permission-id="${permissionId}"]`);
                if (row) {
                    const statusCell = row.querySelector('.permission-status');
                    const actionsCell = row.querySelector('.permission-actions');
                    if (statusCell) {
                        statusCell.innerHTML = approveForm
                            ? '<span class="badge badge-success">Approved</span>'
                            : '<span class="badge badge-danger">Rejected</span>';
                    }
                    if (actionsCell) {
                        actionsCell.innerHTML = '<span class="text-muted">Reviewed</span>';
                    }
                }
                $(`#approveModal${permissionId}`).modal('hide');
                $(`#rejectModal${permissionId}`).modal('hide');
            } else {
                Swal.fire({ icon: 'error', title: 'Failed', text: (data && data.message) ? data.message : 'Operation failed.', confirmButtonColor: '#940000' });
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Failed', text: err.message || 'Operation failed.', confirmButtonColor: '#940000' });
        });
    });
</script>
