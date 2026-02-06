@php
    $user_type = $user_type ?? session('user_type', 'Admin');
    $adminFeedbackContext = $adminFeedbackContext ?? 'teacher';
    $personLabel = $adminFeedbackContext === 'staff' ? 'Staff' : 'Teacher';
    $suggestionsRoute = $adminFeedbackContext === 'staff' ? 'admin.staff.suggestions' : 'admin.suggestions';
    $incidentsRoute = $adminFeedbackContext === 'staff' ? 'admin.staff.incidents' : 'admin.incidents';
    $approveRoute = $adminFeedbackContext === 'staff' ? 'admin.staff.feedback.approve' : 'admin.feedback.approve';
    $rejectRoute = $adminFeedbackContext === 'staff' ? 'admin.staff.feedback.reject' : 'admin.feedback.reject';
    $nameFilterKey = $adminFeedbackContext === 'staff' ? 'staff_name' : 'teacher_name';
    $nameFilterValue = $adminFeedbackContext === 'staff'
        ? ($staffName ?? request('staff_name'))
        : ($teacherName ?? request('teacher_name'));
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
    body, .content, .card, .btn, .form-control, .form-select, .table, .list-group-item {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .card, .alert, .btn, div, .form-control, .form-select {
        border-radius: 0 !important;
    }
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
    .form-control:focus, .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
    }
    .feedback-tabs .nav-link {
        cursor: pointer;
        color: #940000;
        border-radius: 0;
    }
    .feedback-tabs .nav-link.active {
        background: #fff5f5;
        color: #940000;
        font-weight: 600;
        border-color: #940000;
    }
    .feedback-menu .list-group-item {
        cursor: pointer;
        border-left: 4px solid transparent;
    }
    .feedback-menu .list-group-item.active {
        border-left-color: #940000;
        background: #fff5f5;
        color: #940000;
        font-weight: 600;
    }
    .section-title {
        font-weight: 600;
        margin-bottom: 12px;
    }
    .muted-help {
        color: #6c757d;
        font-size: 0.9rem;
    }
    .feedback-body {
        max-height: 70vh;
        overflow-y: auto;
        overflow-x: hidden;
    }
    .feedback-loading {
        display: none;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid rgba(148, 0, 0, 0.25);
        background: rgba(148, 0, 0, 0.05);
        margin-bottom: 12px;
    }
    .feedback-progress {
        position: relative;
        flex: 1;
        height: 8px;
        background: #f0f0f0;
        border-radius: 4px;
        overflow: hidden;
    }
    .feedback-progress::after {
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
    .filter-loading {
        display: none;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid rgba(148, 0, 0, 0.25);
        background: rgba(148, 0, 0, 0.05);
        margin-bottom: 12px;
    }
    .status-pill {
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        display: inline-block;
    }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-approved { background: #d4edda; color: #155724; }
    .status-rejected { background: #f8d7da; color: #721c24; }
</style>

<div class="breadcrumbs">
    <div class="col-sm-6">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Suggestions & Incidents</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header bg-primary-custom text-white">
            <strong>{{ $personLabel }} Suggestions & Incidents (Admin)</strong>
        </div>
        <div class="card-body feedback-body">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="feedback-loading" id="adminFeedbackLoading">
                <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> Saving...</span>
                <div class="feedback-progress"></div>
            </div>
            <div class="filter-loading" id="adminFilterLoading">
                <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> Loading...</span>
                <div class="feedback-progress"></div>
            </div>

            <ul class="nav nav-tabs feedback-tabs mb-3" id="feedbackTabs">
                <li class="nav-item">
                    <a class="nav-link {{ ($activeTab ?? 'suggestions') === 'suggestions' ? 'active' : '' }}" data-tab="suggestions">Suggestions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ ($activeTab ?? '') === 'incidents' ? 'active' : '' }}" data-tab="incidents">Incidents</a>
                </li>
            </ul>

            <div class="row">
                <div class="col-sm-4">
                    <div class="list-group feedback-menu">
                        <a class="list-group-item active" data-section="view">
                            <i class="fa fa-list"></i> View
                        </a>
                        <a class="list-group-item" data-section="report">
                            <i class="fa fa-bar-chart"></i> Report
                        </a>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="feedback-section" data-section="view" data-tab="suggestions">
                        <div class="section-title">Suggestions</div>
                        <form method="GET" action="{{ route($suggestionsRoute) }}" class="mb-3 js-filter-form" data-tab="suggestions" data-section="view">
                            <div class="form-row">
                                <div class="col-sm-4 mb-2">
                                    <label for="admin_suggestion_from">From</label>
                                    <input type="date" class="form-control" id="admin_suggestion_from" name="date_from" value="{{ $dateFrom ?? request('date_from') }}">
                                </div>
                                <div class="col-sm-4 mb-2">
                                    <label for="admin_suggestion_to">To</label>
                                    <input type="date" class="form-control" id="admin_suggestion_to" name="date_to" value="{{ $dateTo ?? request('date_to') }}">
                                </div>
                                <div class="col-sm-4 mb-2">
                                    <label for="admin_suggestion_person">{{ $personLabel }}</label>
                                    <input type="text" class="form-control" id="admin_suggestion_person" name="{{ $nameFilterKey }}" value="{{ $nameFilterValue }}" placeholder="Search {{ strtolower($personLabel) }}">
                                </div>
                                <div class="col-sm-12 mb-2 d-flex align-items-end">
                                    <button class="btn btn-primary-custom w-100" type="submit">
                                        <i class="fa fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                        @if(isset($suggestions) && $suggestions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-primary-custom text-white">
                                        <tr>
                                            <th>Date</th>
                                            <th>{{ $personLabel }}</th>
                                            <th>Message</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($suggestions as $suggestion)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($suggestion->created_at)->format('Y-m-d') }}</td>
                                                <td>{{ $suggestion->person_name ?? $personLabel }}</td>
                                                <td>{{ $suggestion->message }}</td>
                                                <td>
                                                    @if($suggestion->status === 'approved')
                                                        <span class="badge badge-success">Approved</span>
                                                    @elseif($suggestion->status === 'rejected')
                                                        <span class="badge badge-danger">Rejected</span>
                                                    @else
                                                        <span class="badge badge-warning">Pending</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($suggestion->status === 'pending')
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-success"
                                                            data-toggle="modal"
                                                            data-target="#approveModal"
                                                            data-id="{{ $suggestion->feedbackID }}"
                                                        >
                                                            <i class="fa fa-check"></i> Approve
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-danger"
                                                            data-toggle="modal"
                                                            data-target="#rejectModal"
                                                            data-id="{{ $suggestion->feedbackID }}"
                                                        >
                                                            <i class="fa fa-times"></i> Reject
                                                        </button>
                                                    @else
                                                        <span class="muted-help">Processed</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">No suggestions found.</div>
                        @endif
                    </div>

                    <div class="feedback-section d-none" data-section="view" data-tab="incidents">
                        <div class="section-title">Incidents</div>
                        <form method="GET" action="{{ route($incidentsRoute) }}" class="mb-3 js-filter-form" data-tab="incidents" data-section="view">
                            <div class="form-row">
                                <div class="col-sm-4 mb-2">
                                    <label for="admin_incident_from">From</label>
                                    <input type="date" class="form-control" id="admin_incident_from" name="date_from" value="{{ $dateFrom ?? request('date_from') }}">
                                </div>
                                <div class="col-sm-4 mb-2">
                                    <label for="admin_incident_to">To</label>
                                    <input type="date" class="form-control" id="admin_incident_to" name="date_to" value="{{ $dateTo ?? request('date_to') }}">
                                </div>
                                <div class="col-sm-4 mb-2">
                                    <label for="admin_incident_person">{{ $personLabel }}</label>
                                    <input type="text" class="form-control" id="admin_incident_person" name="{{ $nameFilterKey }}" value="{{ $nameFilterValue }}" placeholder="Search {{ strtolower($personLabel) }}">
                                </div>
                                <div class="col-sm-12 mb-2 d-flex align-items-end">
                                    <button class="btn btn-primary-custom w-100" type="submit">
                                        <i class="fa fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                        @if(isset($incidents) && $incidents->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-primary-custom text-white">
                                        <tr>
                                            <th>Date</th>
                                            <th>{{ $personLabel }}</th>
                                            <th>Message</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($incidents as $incident)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($incident->created_at)->format('Y-m-d') }}</td>
                                                <td>{{ $incident->person_name ?? $personLabel }}</td>
                                                <td>{{ $incident->message }}</td>
                                                <td>
                                                    @if($incident->status === 'approved')
                                                        <span class="badge badge-success">Approved</span>
                                                    @elseif($incident->status === 'rejected')
                                                        <span class="badge badge-danger">Rejected</span>
                                                    @else
                                                        <span class="badge badge-warning">Pending</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($incident->status === 'pending')
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-success"
                                                            data-toggle="modal"
                                                            data-target="#approveModal"
                                                            data-id="{{ $incident->feedbackID }}"
                                                        >
                                                            <i class="fa fa-check"></i> Approve
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-danger"
                                                            data-toggle="modal"
                                                            data-target="#rejectModal"
                                                            data-id="{{ $incident->feedbackID }}"
                                                        >
                                                            <i class="fa fa-times"></i> Reject
                                                        </button>
                                                    @else
                                                        <span class="muted-help">Processed</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">No incidents found.</div>
                        @endif
                    </div>

                    <div class="feedback-section d-none" data-section="report" data-tab="suggestions">
                        <div class="section-title">Suggestions Report</div>
                        <form method="GET" action="{{ route($suggestionsRoute) }}" class="mb-3 js-filter-form" data-tab="suggestions" data-section="report">
                            <div class="form-row">
                                <div class="col-sm-4 mb-2">
                                    <label for="admin_report_suggestion_from">From</label>
                                    <input type="date" class="form-control" id="admin_report_suggestion_from" name="date_from" value="{{ $dateFrom ?? request('date_from') }}">
                                </div>
                                <div class="col-sm-4 mb-2">
                                    <label for="admin_report_suggestion_to">To</label>
                                    <input type="date" class="form-control" id="admin_report_suggestion_to" name="date_to" value="{{ $dateTo ?? request('date_to') }}">
                                </div>
                                <div class="col-sm-4 mb-2">
                                    <label for="admin_report_suggestion_person">{{ $personLabel }}</label>
                                    <input type="text" class="form-control" id="admin_report_suggestion_person" name="{{ $nameFilterKey }}" value="{{ $nameFilterValue }}" placeholder="Search {{ strtolower($personLabel) }}">
                                </div>
                                <div class="col-sm-12 mb-2 d-flex align-items-end">
                                    <button class="btn btn-primary-custom w-100" type="submit">
                                        <i class="fa fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Total:</strong> {{ $suggestionStats['total'] ?? 0 }}</div>
                            <div class="col-sm-4"><strong>Pending:</strong> {{ $suggestionStats['pending'] ?? 0 }}</div>
                            <div class="col-sm-4"><strong>Approved:</strong> {{ $suggestionStats['approved'] ?? 0 }}</div>
                            <div class="col-sm-4"><strong>Rejected:</strong> {{ $suggestionStats['rejected'] ?? 0 }}</div>
                        </div>
                    </div>

                    <div class="feedback-section d-none" data-section="report" data-tab="incidents">
                        <div class="section-title">Incidents Report</div>
                        <form method="GET" action="{{ route($incidentsRoute) }}" class="mb-3 js-filter-form" data-tab="incidents" data-section="report">
                            <div class="form-row">
                                <div class="col-sm-4 mb-2">
                                    <label for="admin_report_incident_from">From</label>
                                    <input type="date" class="form-control" id="admin_report_incident_from" name="date_from" value="{{ $dateFrom ?? request('date_from') }}">
                                </div>
                                <div class="col-sm-4 mb-2">
                                    <label for="admin_report_incident_to">To</label>
                                    <input type="date" class="form-control" id="admin_report_incident_to" name="date_to" value="{{ $dateTo ?? request('date_to') }}">
                                </div>
                                <div class="col-sm-4 mb-2">
                                    <label for="admin_report_incident_person">{{ $personLabel }}</label>
                                    <input type="text" class="form-control" id="admin_report_incident_person" name="{{ $nameFilterKey }}" value="{{ $nameFilterValue }}" placeholder="Search {{ strtolower($personLabel) }}">
                                </div>
                                <div class="col-sm-12 mb-2 d-flex align-items-end">
                                    <button class="btn btn-primary-custom w-100" type="submit">
                                        <i class="fa fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Total:</strong> {{ $incidentStats['total'] ?? 0 }}</div>
                            <div class="col-sm-4"><strong>Pending:</strong> {{ $incidentStats['pending'] ?? 0 }}</div>
                            <div class="col-sm-4"><strong>Approved:</strong> {{ $incidentStats['approved'] ?? 0 }}</div>
                            <div class="col-sm-4"><strong>Rejected:</strong> {{ $incidentStats['rejected'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="approveModalLabel"><i class="fa fa-check"></i> Approve</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route($approveRoute) }}" id="approveForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="feedbackID" id="approve_feedback_id">
                    <div class="form-group mb-3">
                        <label for="approve_response">Description</label>
                        <textarea class="form-control" id="approve_response" name="admin_response" rows="3" required></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="approve_due_date">Planned Date</label>
                        <input type="date" class="form-control" id="approve_due_date" name="response_due_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="fa fa-save"></i> Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="rejectModalLabel"><i class="fa fa-times"></i> Reject</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route($rejectRoute) }}" id="rejectForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="feedbackID" id="reject_feedback_id">
                    <div class="form-group mb-3">
                        <label for="reject_reason">Reason</label>
                        <textarea class="form-control" id="reject_reason" name="admin_response" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="fa fa-save"></i> Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        const loadingBar = document.getElementById('adminFeedbackLoading');
        const approveForm = document.getElementById('approveForm');
        const rejectForm = document.getElementById('rejectForm');

        function showAlert(status, message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: status,
                    title: status === 'success' ? 'Success' : 'Failed',
                    text: message
                }).then(() => {
                    if (status === 'success') {
                        window.location.reload();
                    }
                });
            } else {
                alert(message);
                if (status === 'success') {
                    window.location.reload();
                }
            }
        }

        function handleAjaxSubmit(form) {
            if (!form) return;
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (loadingBar) {
                    loadingBar.style.display = 'flex';
                }
                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.success) {
                        showAlert('success', data.message || 'Saved successfully.');
                    } else {
                        showAlert('error', (data && data.message) ? data.message : 'Failed to save.');
                    }
                })
                .catch(() => {
                    showAlert('error', 'Failed to save.');
                })
                .finally(() => {
                    if (loadingBar) {
                        loadingBar.style.display = 'none';
                    }
                });
            });
        }

        handleAjaxSubmit(approveForm);
        handleAjaxSubmit(rejectForm);

        const adminFilterLoading = document.getElementById('adminFilterLoading');
        const filterForms = document.querySelectorAll('.js-filter-form');
        if (filterForms.length) {
            filterForms.forEach(form => {
                form.addEventListener('submit', () => {
                    const tabValue = form.getAttribute('data-tab') || activeTab;
                    const sectionValue = form.getAttribute('data-section') || activeSection;
                    if (tabValue) {
                        let tabInput = form.querySelector('input[name="tab"]');
                        if (!tabInput) {
                            tabInput = document.createElement('input');
                            tabInput.type = 'hidden';
                            tabInput.name = 'tab';
                            form.appendChild(tabInput);
                        }
                        tabInput.value = tabValue;
                    }
                    if (sectionValue) {
                        let sectionInput = form.querySelector('input[name="section"]');
                        if (!sectionInput) {
                            sectionInput = document.createElement('input');
                            sectionInput.type = 'hidden';
                            sectionInput.name = 'section';
                            form.appendChild(sectionInput);
                        }
                        sectionInput.value = sectionValue;
                    }
                    if (adminFilterLoading) {
                        adminFilterLoading.style.display = 'flex';
                    }
                });
            });
        }

        const tabs = document.querySelectorAll('.feedback-tabs .nav-link');
        const menuItems = document.querySelectorAll('.feedback-menu .list-group-item');
        const sections = document.querySelectorAll('.feedback-section');

        let activeTab = "{{ $activeTab ?? 'suggestions' }}";
        let activeSection = "{{ $activeSection ?? request('section', 'view') }}";

        function showSections() {
            sections.forEach(section => {
                const sectionName = section.getAttribute('data-section');
                const tabName = section.getAttribute('data-tab');
                if (sectionName === activeSection && tabName === activeTab) {
                    section.classList.remove('d-none');
                } else {
                    section.classList.add('d-none');
                }
            });
        }

        function setActiveTab(tabName) {
            activeTab = tabName;
            tabs.forEach(tab => tab.classList.toggle('active', tab.getAttribute('data-tab') === tabName));
            showSections();
        }

        function setActiveSection(sectionName) {
            activeSection = sectionName;
            menuItems.forEach(item => item.classList.toggle('active', item.getAttribute('data-section') === sectionName));
            showSections();
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                setActiveTab(this.getAttribute('data-tab'));
            });
        });

        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                setActiveSection(this.getAttribute('data-section'));
            });
        });

        setActiveTab(activeTab);
        setActiveSection(activeSection);

        const approveButtons = document.querySelectorAll('[data-target="#approveModal"]');
        approveButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('approve_feedback_id').value = this.getAttribute('data-id');
                document.getElementById('approve_response').value = '';
                document.getElementById('approve_due_date').value = '';
            });
        });

        const rejectButtons = document.querySelectorAll('[data-target="#rejectModal"]');
        rejectButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('reject_feedback_id').value = this.getAttribute('data-id');
                document.getElementById('reject_reason').value = '';
            });
        });
    })();
</script>
