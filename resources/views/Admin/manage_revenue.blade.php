@if($user_type == 'Admin')
@include('includes.Admin_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    body, .content, .card, .btn, .form-control, .form-select, .table, .list-group-item {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    /* Remove border-radius from all widgets */
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

    .revenue-menu .list-group-item {
        cursor: pointer;
        border-left: 4px solid transparent;
    }
    .revenue-menu .list-group-item.active {
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
</style>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Manage Revenue</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header bg-primary-custom text-white">
            <strong>Revenue Management</strong>
        </div>
        <div class="card-body">
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
            <div class="row">
                <div class="col-sm-4">
                    <div class="list-group revenue-menu">
                        <a class="list-group-item active" data-target="#section-fees">
                            <i class="fa fa-money"></i> Source of Income (Fees)
                        </a>
                        <a class="list-group-item" data-target="#section-add-source">
                            <i class="fa fa-plus-circle"></i> Add New Source of Income
                        </a>
                        <a class="list-group-item" data-target="#section-record-revenue">
                            <i class="fa fa-pencil-square-o"></i> Record Revenue
                        </a>
                        <a class="list-group-item" data-target="#section-revenue-report">
                            <i class="fa fa-bar-chart"></i> Revenue Report
                        </a>
                    </div>
                    <div class="card border-primary-custom mt-3">
                        <div class="card-body">
                            <div class="section-title">Quick Notes</div>
                            <div class="muted-help">
                                - Default source of income is Fees.<br>
                                - Add other income sources like Transport, Uniform, Meals.<br>
                                - Use Revenue Report to see yearly totals.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div id="section-fees" class="revenue-section">
                        <div class="section-title">Fees Income (Default)</div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fees_year" class="form-label">Year</label>
                                <select class="form-select" id="fees_year">
                                    <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                                    <option value="{{ date('Y') - 1 }}">{{ date('Y') - 1 }}</option>
                                    <option value="{{ date('Y') - 2 }}">{{ date('Y') - 2 }}</option>
                                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button class="btn btn-primary-custom w-100" type="button" id="loadFeesIncomeBtn">
                                    <i class="fa fa-search"></i> View Fees Income
                                </button>
                            </div>
                        </div>
                        <div class="alert alert-info" id="feesIncomeInfo">
                            Select year to view fees collected. This will show total fees collected within the year.
                        </div>
                        <table class="table table-bordered">
                            <thead class="bg-primary-custom text-white">
                                <tr>
                                    <th>Year</th>
                                    <th>Expected Fees</th>
                                    <th>Collected Fees</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="feesYearLabel">{{ date('Y') }}</td>
                                    <td id="feesTotalRequired">TZS 0</td>
                                    <td id="feesTotalPaid">TZS 0</td>
                                    <td id="feesTotalBalance">TZS 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="section-add-source" class="revenue-section d-none">
                        <div class="section-title">Add New Source of Income</div>
                        <form method="POST" action="{{ route('revenue_sources.store') }}" class="mb-4">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="source_name">Source Name</label>
                                <input type="text" class="form-control" id="source_name" name="source_name" value="{{ old('source_name') }}" placeholder="e.g. Transport, Uniform, Meals" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="source_type">Type</label>
                                <select class="form-select" id="source_type" name="source_type" required>
                                    <option value="fixed">Fixed Amount</option>
                                    <option value="per_item">Per Item / Quantity</option>
                                    <option value="variable">Variable</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="source_amount">Default Amount</label>
                                <input type="number" class="form-control" id="source_amount" name="default_amount" value="{{ old('default_amount') }}" placeholder="e.g. 50000" min="0" step="0.01">
                            </div>
                            <div class="form-group mb-3">
                                <label for="source_description">Description</label>
                                <textarea class="form-control" id="source_description" name="description" rows="3" placeholder="Short description about this income source">{{ old('description') }}</textarea>
                            </div>
                            <button class="btn btn-primary-custom" type="submit">
                                <i class="fa fa-save"></i> Save Source of Income
                            </button>
                        </form>

                        <div class="section-title">Existing Sources</div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-primary-custom text-white">
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Default Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($revenueSources ?? []) as $source)
                                        <tr>
                                            <td>{{ $source->source_name }}</td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $source->source_type)) }}</td>
                                            <td>{{ $source->default_amount !== null ? number_format($source->default_amount, 0) : '-' }}</td>
                                            <td>{{ $source->status }}</td>
                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-toggle="modal"
                                                    data-target="#editSourceModal"
                                                    data-id="{{ $source->revenue_sourceID }}"
                                                    data-name="{{ $source->source_name }}"
                                                    data-type="{{ $source->source_type }}"
                                                    data-amount="{{ $source->default_amount }}"
                                                    data-description="{{ $source->description }}"
                                                    data-status="{{ $source->status }}"
                                                >
                                                    <i class="fa fa-pencil"></i> Edit
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No sources added yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="section-record-revenue" class="revenue-section d-none">
                        <div class="section-title">Record Revenue</div>
                        <form method="POST" action="{{ route('revenue_records.store') }}">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="record_date">Date</label>
                                <input type="date" class="form-control" id="record_date" name="record_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="record_source">Source of Income</label>
                                <select class="form-select" id="record_source" name="revenue_sourceID" required>
                                    @forelse(($revenueSources ?? []) as $source)
                                        <option
                                            value="{{ $source->revenue_sourceID }}"
                                            data-type="{{ $source->source_type }}"
                                            data-amount="{{ $source->default_amount }}"
                                        >
                                            {{ $source->source_name }}
                                        </option>
                                    @empty
                                        <option value="" disabled selected>No sources added yet</option>
                                    @endforelse
                                </select>
                                <small class="muted-help">This list should come from source of income registration.</small>
                            </div>
                            <div class="form-group mb-3">
                                <label for="record_amount">Amount</label>
                                <input type="number" class="form-control" id="record_amount" name="amount" placeholder="Amount per unit or total amount" min="0" step="0.01">
                            </div>
                            <div class="form-group mb-3">
                                <label for="record_quantity">Quantity (optional)</label>
                                <input type="number" class="form-control" id="record_quantity" name="quantity" placeholder="e.g. 20 students, 5 items" min="0" step="1">
                            </div>
                            <div class="form-group mb-3">
                                <label for="record_note">Note</label>
                                <textarea class="form-control" id="record_note" name="note" rows="3" placeholder="Additional details for this revenue"></textarea>
                            </div>
                            <button class="btn btn-primary-custom" type="submit">
                                <i class="fa fa-check"></i> Record Revenue
                            </button>
                        </form>
                    </div>

                    <div id="section-revenue-report" class="revenue-section d-none">
                        <div class="section-title">Revenue Report (Yearly)</div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="report_year" class="form-label">Year</label>
                                <select class="form-select" id="report_year">
                                    <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                                    <option value="{{ date('Y') - 1 }}">{{ date('Y') - 1 }}</option>
                                    <option value="{{ date('Y') - 2 }}">{{ date('Y') - 2 }}</option>
                                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button class="btn btn-primary-custom w-100" type="button" id="loadRevenueReportBtn">
                                    <i class="fa fa-file-text-o"></i> Generate Report
                                </button>
                            </div>
                        </div>
                        <table class="table table-bordered">
                            <thead class="bg-primary-custom text-white">
                                <tr>
                                    <th>Source of Income</th>
                                    <th>Total Collected</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr data-source="fees">
                                    <td>Fees</td>
                                    <td id="reportFeesTotal">TZS 0</td>
                                </tr>
                                @forelse(($revenueSources ?? []) as $source)
                                    <tr data-source-id="{{ $source->revenue_sourceID }}">
                                        <td>{{ $source->source_name }}</td>
                                        <td class="report-source-total" data-value="0">TZS 0</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No additional sources added yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Grand Total</th>
                                    <th id="reportGrandTotal">TZS 0</th>
                                </tr>
                            </tfoot>
                        </table>
                        <div class="alert alert-info">
                            This report aggregates all revenue sources (Fees + other sources) for the selected year.
                            This will support the expenses module to compute net balance.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<!-- Edit Source Modal -->
<div class="modal fade" id="editSourceModal" tabindex="-1" role="dialog" aria-labelledby="editSourceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editSourceModalLabel"><i class="fa fa-pencil"></i> Update Source of Income</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('revenue_sources.update') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="revenue_sourceID" id="edit_source_id">
                    <div class="form-group mb-3">
                        <label for="edit_source_name">Source Name</label>
                        <input type="text" class="form-control" id="edit_source_name" name="source_name" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_source_type">Type</label>
                        <select class="form-select" id="edit_source_type" name="source_type" required>
                            <option value="fixed">Fixed Amount</option>
                            <option value="per_item">Per Item / Quantity</option>
                            <option value="variable">Variable</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_source_amount">Default Amount</label>
                        <input type="number" class="form-control" id="edit_source_amount" name="default_amount" min="0" step="0.01">
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_source_description">Description</label>
                        <textarea class="form-control" id="edit_source_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label for="edit_source_status">Status</label>
                        <select class="form-select" id="edit_source_status" name="status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="fa fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        const menuItems = document.querySelectorAll('.revenue-menu .list-group-item');
        const sections = document.querySelectorAll('.revenue-section');
        const feesYearSelect = document.getElementById('fees_year');
        const feesYearLabel = document.getElementById('feesYearLabel');
        const feesTotalRequired = document.getElementById('feesTotalRequired');
        const feesTotalPaid = document.getElementById('feesTotalPaid');
        const feesTotalBalance = document.getElementById('feesTotalBalance');
        const feesIncomeInfo = document.getElementById('feesIncomeInfo');
        const loadFeesIncomeBtn = document.getElementById('loadFeesIncomeBtn');
        const feesEndpoint = "{{ route('get_payments_ajax') }}";
        const recordSource = document.getElementById('record_source');
        const recordAmount = document.getElementById('record_amount');
        const recordQuantity = document.getElementById('record_quantity');
        const reportYearSelect = document.getElementById('report_year');
        const loadRevenueReportBtn = document.getElementById('loadRevenueReportBtn');
        const reportFeesTotal = document.getElementById('reportFeesTotal');
        const reportGrandTotal = document.getElementById('reportGrandTotal');
        const revenueReportEndpoint = "{{ route('revenue_report.data') }}";

        function showSection(targetId) {
            sections.forEach(section => {
                if (section.id === targetId) {
                    section.classList.remove('d-none');
                } else {
                    section.classList.add('d-none');
                }
            });
        }

        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                menuItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                const target = this.getAttribute('data-target');
                if (target) {
                    showSection(target.replace('#', ''));
                }
            });
        });

        function formatMoney(value) {
            const amount = Number(value || 0);
            return 'TZS ' + amount.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
        }

        function setFeesLoading(isLoading) {
            if (!feesIncomeInfo) return;
            if (isLoading) {
                feesIncomeInfo.classList.remove('alert-info', 'alert-danger');
                feesIncomeInfo.classList.add('alert-warning');
                feesIncomeInfo.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading fees income...';
            } else {
                feesIncomeInfo.classList.remove('alert-warning', 'alert-danger');
                feesIncomeInfo.classList.add('alert-info');
                feesIncomeInfo.textContent = 'Select year to view fees collected. This will show total fees collected within the year.';
            }
        }

        function loadFeesIncome() {
            const year = feesYearSelect ? feesYearSelect.value : "{{ date('Y') }}";
            if (feesYearLabel) feesYearLabel.textContent = year;
            setFeesLoading(true);

            const url = new URL(feesEndpoint, window.location.origin);
            url.searchParams.set('year', year);

            fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (!data || !data.success) {
                        throw new Error(data && data.message ? data.message : 'Failed to load fees income.');
                    }
                    const stats = data.statistics || {};
                    if (feesTotalRequired) feesTotalRequired.textContent = formatMoney(stats.total_required || 0);
                    if (feesTotalPaid) feesTotalPaid.textContent = formatMoney(stats.total_paid || 0);
                    if (feesTotalBalance) feesTotalBalance.textContent = formatMoney(stats.total_balance || 0);
                    setFeesLoading(false);
                })
                .catch(error => {
                    if (feesIncomeInfo) {
                        feesIncomeInfo.classList.remove('alert-info', 'alert-warning');
                        feesIncomeInfo.classList.add('alert-danger');
                        feesIncomeInfo.textContent = error.message || 'Failed to load fees income.';
                    }
                });
        }

        if (loadFeesIncomeBtn) {
            loadFeesIncomeBtn.addEventListener('click', loadFeesIncome);
        }

        if (feesYearSelect) {
            feesYearSelect.addEventListener('change', loadFeesIncome);
        }

        loadFeesIncome();

        function updateRevenueAmount() {
            if (!recordSource || !recordAmount || !recordQuantity) return;
            const selected = recordSource.options[recordSource.selectedIndex];
            if (!selected) return;

            const sourceType = selected.getAttribute('data-type');
            const defaultAmount = parseFloat(selected.getAttribute('data-amount')) || 0;
            const qty = parseFloat(recordQuantity.value) || 0;

            if (sourceType === 'per_item') {
                recordQuantity.disabled = false;
                recordQuantity.placeholder = 'Enter quantity';
                if (qty > 0) {
                    recordAmount.value = (defaultAmount * qty).toFixed(2);
                } else {
                    recordAmount.value = defaultAmount ? defaultAmount.toFixed(2) : '';
                }
                recordAmount.readOnly = true;
            } else if (sourceType === 'fixed') {
                recordQuantity.value = '';
                recordQuantity.disabled = true;
                recordQuantity.placeholder = 'Not required';
                recordAmount.value = defaultAmount ? defaultAmount.toFixed(2) : '';
                recordAmount.readOnly = true;
            } else {
                recordQuantity.disabled = true;
                recordQuantity.value = '';
                recordQuantity.placeholder = 'Not required';
                recordAmount.readOnly = false;
                if (!recordAmount.value) {
                    recordAmount.value = defaultAmount ? defaultAmount.toFixed(2) : '';
                }
            }
        }

        if (recordSource) {
            recordSource.addEventListener('change', updateRevenueAmount);
        }
        if (recordQuantity) {
            recordQuantity.addEventListener('input', updateRevenueAmount);
        }
        updateRevenueAmount();

        const editButtons = document.querySelectorAll('[data-target="#editSourceModal"]');
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit_source_id').value = this.getAttribute('data-id');
                document.getElementById('edit_source_name').value = this.getAttribute('data-name') || '';
                document.getElementById('edit_source_type').value = this.getAttribute('data-type') || 'fixed';
                document.getElementById('edit_source_amount').value = this.getAttribute('data-amount') || '';
                document.getElementById('edit_source_description').value = this.getAttribute('data-description') || '';
                document.getElementById('edit_source_status').value = this.getAttribute('data-status') || 'Active';
            });
        });

        function calculateGrandTotal() {
            let total = 0;
            const feeValue = reportFeesTotal ? Number(reportFeesTotal.getAttribute('data-value') || 0) : 0;
            total += feeValue;

            document.querySelectorAll('.report-source-total').forEach(cell => {
                total += Number(cell.getAttribute('data-value') || 0);
            });

            if (reportGrandTotal) {
                reportGrandTotal.textContent = formatMoney(total);
            }
        }

        function loadRevenueReport() {
            const year = reportYearSelect ? reportYearSelect.value : "{{ date('Y') }}";
            const url = new URL(feesEndpoint, window.location.origin);
            url.searchParams.set('year', year);

            fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (!data || !data.success) {
                        throw new Error(data && data.message ? data.message : 'Failed to load report.');
                    }
                    const stats = data.statistics || {};
                    const feesPaid = Number(stats.total_paid || 0);
                    if (reportFeesTotal) {
                        reportFeesTotal.textContent = formatMoney(feesPaid);
                        reportFeesTotal.setAttribute('data-value', feesPaid);
                    }
                    calculateGrandTotal();
                })
                .catch(() => {
                    if (reportFeesTotal) {
                        reportFeesTotal.textContent = formatMoney(0);
                        reportFeesTotal.setAttribute('data-value', 0);
                    }
                    calculateGrandTotal();
                });

            const sourcesUrl = new URL(revenueReportEndpoint, window.location.origin);
            sourcesUrl.searchParams.set('year', year);
            fetch(sourcesUrl.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (!data || !data.success) {
                        throw new Error('Failed to load source totals');
                    }
                    document.querySelectorAll('.report-source-total').forEach(cell => {
                        const row = cell.closest('tr');
                        const sourceId = row ? row.getAttribute('data-source-id') : null;
                        const total = sourceId && data.data && data.data[sourceId] ? Number(data.data[sourceId]) : 0;
                        cell.textContent = formatMoney(total);
                        cell.setAttribute('data-value', total);
                    });
                    calculateGrandTotal();
                })
                .catch(() => {
                    document.querySelectorAll('.report-source-total').forEach(cell => {
                        cell.textContent = formatMoney(0);
                        cell.setAttribute('data-value', 0);
                    });
                    calculateGrandTotal();
                });
        }

        if (loadRevenueReportBtn) {
            loadRevenueReportBtn.addEventListener('click', loadRevenueReport);
        }
        if (reportYearSelect) {
            reportYearSelect.addEventListener('change', loadRevenueReport);
        }
        loadRevenueReport();
    })();
</script>
