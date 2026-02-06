@php
    $user_type = $user_type ?? session('user_type', 'Teacher');
    $feedbackContext = $feedbackContext ?? ($user_type === 'Staff' ? 'staff' : 'teacher');
    $feedbackLabel = $feedbackContext === 'staff' ? 'Staff' : 'Teacher';
    $suggestionsRoute = $feedbackContext === 'staff' ? 'staff.suggestions' : 'teacher.suggestions';
    $incidentsRoute = $feedbackContext === 'staff' ? 'staff.incidents' : 'teacher.incidents';
    $storeRoute = $feedbackContext === 'staff' ? 'staff.feedback.store' : 'teacher.feedback.store';
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
            <strong>{{ $feedbackLabel }} Suggestions & Incidents</strong>
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
            <div class="feedback-loading" id="suggestionLoading">
                <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> Sending suggestion...</span>
                <div class="feedback-progress"></div>
            </div>
            <div class="feedback-loading" id="incidentLoading">
                <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> Sending incident...</span>
                <div class="feedback-progress"></div>
            </div>
            <div class="filter-loading" id="teacherFilterLoading">
                <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> Loading...</span>
                <div class="feedback-progress"></div>
            </div>

            <ul class="nav nav-tabs feedback-tabs mb-3" id="feedbackTabs">
                <li class="nav-item">
                    <a class="nav-link active" data-tab="suggestions">Suggestions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-tab="incidents">Incidents</a>
                </li>
            </ul>

            <div class="row">
                <div class="col-sm-4">
                    <div class="list-group feedback-menu">
                        <a class="list-group-item active" data-section="send">
                            <i class="fa fa-paper-plane"></i> Send
                        </a>
                        <a class="list-group-item" data-section="view">
                            <i class="fa fa-list"></i> View
                        </a>
                        <a class="list-group-item" data-section="report">
                            <i class="fa fa-bar-chart"></i> Report
                        </a>
                    </div>
                    <div class="card border-primary-custom mt-3">
                        <div class="card-body">
                            <div class="section-title">Quick Notes</div>
                            <div class="muted-help">
                                - Messages go to the Admin for review.<br>
                                - You will see status updates here.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="feedback-section" data-section="send" data-tab="suggestions">
                        <div class="section-title">Send Your Suggestion</div>
                        <form method="POST" action="{{ route($storeRoute) }}" id="suggestionForm">
                            @csrf
                            <input type="hidden" name="type" value="suggestion">
                            <div class="form-group mb-3">
                                <label for="suggestion_message">Message</label>
                                <textarea class="form-control" id="suggestion_message" name="message" rows="4" placeholder="Type your suggestion..." required></textarea>
                            </div>
                            <button class="btn btn-primary-custom" type="submit">
                                <i class="fa fa-paper-plane"></i> Send Suggestion
                            </button>
                        </form>
                    </div>

                    <div class="feedback-section d-none" data-section="send" data-tab="incidents">
                        <div class="section-title">Report an Incident</div>
                        <form method="POST" action="{{ route($storeRoute) }}" id="incidentForm">
                            @csrf
                            <input type="hidden" name="type" value="incident">
                            <div class="form-group mb-3">
                                <label for="incident_message">Message</label>
                                <textarea class="form-control" id="incident_message" name="message" rows="4" placeholder="Describe the incident..." required></textarea>
                            </div>
                            <button class="btn btn-primary-custom" type="submit">
                                <i class="fa fa-paper-plane"></i> Send Incident
                            </button>
                        </form>
                    </div>

                    <div class="feedback-section d-none" data-section="view" data-tab="suggestions">
                        <div class="section-title">My Suggestions</div>
                        <form method="GET" action="{{ route($suggestionsRoute) }}" class="mb-3 js-filter-form" data-tab="suggestions" data-section="view">
                            <div class="form-row">
                                <div class="col-sm-4 mb-2">
                                    <label for="suggestion_from">From</label>
                                    <input type="date" class="form-control" id="suggestion_from" name="date_from" value="{{ $dateFrom ?? request('date_from') }}">
                                </div>
                                <div class="col-sm-4 mb-2">
                                    <label for="suggestion_to">To</label>
                                    <input type="date" class="form-control" id="suggestion_to" name="date_to" value="{{ $dateTo ?? request('date_to') }}">
                                </div>
                                <div class="col-sm-4 mb-2 d-flex align-items-end">
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
                                            <th>Message</th>
                                            <th>Status</th>
                                            <th>Response</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($suggestions as $suggestion)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($suggestion->created_at)->format('Y-m-d') }}</td>
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
                                                    @if($suggestion->admin_response)
                                                        {{ $suggestion->admin_response }}
                                                        @if($suggestion->response_due_date)
                                                            <br><small class="muted-help">Planned: {{ $suggestion->response_due_date }}</small>
                                                        @endif
                                                    @else
                                                        <span class="muted-help">Pending review</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">No suggestions submitted yet.</div>
                        @endif
                    </div>

                    <div class="feedback-section d-none" data-section="view" data-tab="incidents">
                        <div class="section-title">My Incidents</div>
                        <form method="GET" action="{{ route($incidentsRoute) }}" class="mb-3 js-filter-form" data-tab="incidents" data-section="view">
                            <div class="form-row">
                                <div class="col-sm-4 mb-2">
                                    <label for="incident_from">From</label>
                                    <input type="date" class="form-control" id="incident_from" name="date_from" value="{{ $dateFrom ?? request('date_from') }}">
                                </div>
                                <div class="col-sm-4 mb-2">
                                    <label for="incident_to">To</label>
                                    <input type="date" class="form-control" id="incident_to" name="date_to" value="{{ $dateTo ?? request('date_to') }}">
                                </div>
                                <div class="col-sm-4 mb-2 d-flex align-items-end">
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
                                            <th>Message</th>
                                            <th>Status</th>
                                            <th>Response</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($incidents as $incident)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($incident->created_at)->format('Y-m-d') }}</td>
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
                                                    @if($incident->admin_response)
                                                        {{ $incident->admin_response }}
                                                        @if($incident->response_due_date)
                                                            <br><small class="muted-help">Planned: {{ $incident->response_due_date }}</small>
                                                        @endif
                                                    @else
                                                        <span class="muted-help">Pending review</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">No incidents submitted yet.</div>
                        @endif
                    </div>

                    <div class="feedback-section d-none" data-section="report" data-tab="suggestions">
                        <div class="section-title">Suggestions Report</div>
                        <form method="GET" action="{{ route($suggestionsRoute) }}" class="mb-3 js-filter-form" data-tab="suggestions" data-section="report">
                            <div class="form-row">
                                <div class="col-sm-4 mb-2">
                                    <label for="report_suggestion_from">From</label>
                                    <input type="date" class="form-control" id="report_suggestion_from" name="date_from" value="{{ $dateFrom ?? request('date_from') }}">
                                </div>
                                <div class="col-sm-4 mb-2">
                                    <label for="report_suggestion_to">To</label>
                                    <input type="date" class="form-control" id="report_suggestion_to" name="date_to" value="{{ $dateTo ?? request('date_to') }}">
                                </div>
                                <div class="col-sm-4 mb-2 d-flex align-items-end">
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
                        @if(isset($suggestions) && $suggestions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-primary-custom text-white">
                                        <tr>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Message</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($suggestions as $suggestion)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($suggestion->created_at)->format('Y-m-d') }}</td>
                                                <td>
                                                    @if($suggestion->status === 'approved')
                                                        <span class="badge badge-success">Approved</span>
                                                    @elseif($suggestion->status === 'rejected')
                                                        <span class="badge badge-danger">Rejected</span>
                                                    @else
                                                        <span class="badge badge-warning">Pending</span>
                                                    @endif
                                                </td>
                                                <td>{{ $suggestion->message }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div class="feedback-section d-none" data-section="report" data-tab="incidents">
                        <div class="section-title">Incidents Report</div>
                        <form method="GET" action="{{ route($incidentsRoute) }}" class="mb-3 js-filter-form" data-tab="incidents" data-section="report">
                            <div class="form-row">
                                <div class="col-sm-4 mb-2">
                                    <label for="report_incident_from">From</label>
                                    <input type="date" class="form-control" id="report_incident_from" name="date_from" value="{{ $dateFrom ?? request('date_from') }}">
                                </div>
                                <div class="col-sm-4 mb-2">
                                    <label for="report_incident_to">To</label>
                                    <input type="date" class="form-control" id="report_incident_to" name="date_to" value="{{ $dateTo ?? request('date_to') }}">
                                </div>
                                <div class="col-sm-4 mb-2 d-flex align-items-end">
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
                        @if(isset($incidents) && $incidents->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-primary-custom text-white">
                                        <tr>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Message</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($incidents as $incident)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($incident->created_at)->format('Y-m-d') }}</td>
                                                <td>
                                                    @if($incident->status === 'approved')
                                                        <span class="badge badge-success">Approved</span>
                                                    @elseif($incident->status === 'rejected')
                                                        <span class="badge badge-danger">Rejected</span>
                                                    @else
                                                        <span class="badge badge-warning">Pending</span>
                                                    @endif
                                                </td>
                                                <td>{{ $incident->message }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<script>
    (function() {
        const suggestionLoading = document.getElementById('suggestionLoading');
        const incidentLoading = document.getElementById('incidentLoading');

        function showAlert(status, message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: status,
                    title: status === 'success' ? 'Sent!' : 'Failed',
                    text: message
                });
            } else {
                alert(message);
            }
        }

        const feedbackFormConfig = {
            suggestionForm: {
                loading: suggestionLoading,
                successMessage: 'Suggestion sent successfully.'
            },
            incidentForm: {
                loading: incidentLoading,
                successMessage: 'Incident sent successfully.'
            }
        };

        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form || !feedbackFormConfig[form.id]) {
                return;
            }
            e.preventDefault();
            const config = feedbackFormConfig[form.id];
            if (config.loading) {
                config.loading.style.display = 'flex';
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
                    form.reset();
                    showAlert('success', data.message || config.successMessage);
                } else {
                    showAlert('error', (data && data.message) ? data.message : 'Failed to send.');
                }
            })
            .catch(() => {
                showAlert('error', 'Failed to send.');
            })
            .finally(() => {
                if (config.loading) {
                    config.loading.style.display = 'none';
                }
            });
        }, true);

        const teacherFilterLoading = document.getElementById('teacherFilterLoading');
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
                    if (teacherFilterLoading) {
                        teacherFilterLoading.style.display = 'flex';
                    }
                });
            });
        }

        const tabs = document.querySelectorAll('.feedback-tabs .nav-link');
        const menuItems = document.querySelectorAll('.feedback-menu .list-group-item');
        const sections = document.querySelectorAll('.feedback-section');

        let activeTab = "{{ $activeTab ?? 'suggestions' }}";
        let activeSection = "{{ $activeSection ?? request('section', 'send') }}";

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
    })();
</script>
