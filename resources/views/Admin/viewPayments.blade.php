@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

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
    .student-photo {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #940000;
    }
    .student-photo-placeholder {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 16px;
        border: 2px solid #940000;
    }
    .photo-container {
        position: relative;
        display: inline-block;
    }
    .control-number {
        font-family: 'Courier New', monospace;
        font-weight: 700;
        color: #940000;
    }
    .view-more-details {
        display: none;
    }
    /* Ensure table fits without horizontal scroll */
    #paymentsTable {
        font-size: 0.9rem;
        width: 100% !important;
        table-layout: fixed;
    }
    #paymentsTable th,
    #paymentsTable td {
        padding: 8px 10px;
        vertical-align: middle;
        word-wrap: break-word;
    }
    #paymentsTable th:nth-child(1),
    #paymentsTable td:nth-child(1) {
        width: 30%;
    }
    #paymentsTable th:nth-child(2),
    #paymentsTable td:nth-child(2) {
        width: 15%;
        text-align: center;
    }
    #paymentsTable th:nth-child(3),
    #paymentsTable td:nth-child(3) {
        width: 20%;
        text-align: right;
    }
    #paymentsTable th:nth-child(4),
    #paymentsTable td:nth-child(4) {
        width: 20%;
        text-align: right;
    }
    #paymentsTable th:nth-child(5),
    #paymentsTable td:nth-child(5) {
        width: 15%;
        text-align: center;
    }
    #paymentsTable th:nth-child(8),
    #paymentsTable td:nth-child(8) {
        width: 10%;
    }
    #paymentsTable th:nth-child(9),
    #paymentsTable td:nth-child(9) {
        width: 10%;
    }
    #paymentsTable .btn-group-sm .btn {
        padding: 0.2rem 0.4rem;
        font-size: 0.875rem;
    }
    @media (max-width: 768px) {
        #paymentsTable {
            font-size: 0.8rem;
        }
        #paymentsTable th,
        #paymentsTable td {
            padding: 6px 8px;
        }
    }
    .border-primary-custom {
        border: 1px solid #940000 !important;
    }
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Page Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body bg-primary-custom text-white rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-credit-card"></i> Payments & Control Numbers
                        </h4>
                        <div class="d-flex gap-2">
                            <button class="btn btn-light text-primary-custom fw-bold" type="button" id="generateControlNumbersBtn">
                                <i class="bi bi-key"></i> Generate Control Numbers
                            </button>
                            <button class="btn btn-light text-primary-custom fw-bold" type="button" id="sendSMSBtn">
                                <i class="bi bi-send-check"></i> Send Control Numbers
                            </button>
                            <button class="btn btn-light text-warning fw-bold" type="button" id="sendDebtRemindersBtn">
                                <i class="bi bi-bell-fill"></i> Send Debt Reminders
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4" id="statisticsCards">
                <div class="col-md-2" style="flex: 0 0 20%; max-width: 20%;">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Pending</h6>
                            <h3 class="mb-0 text-warning" id="statPendingPayments">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2" style="flex: 0 0 20%; max-width: 20%;">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Incomplete</h6>
                            <h3 class="mb-0 text-info" id="statIncompletePayments">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2" style="flex: 0 0 20%; max-width: 20%;">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Fully Paid</h6>
                            <h3 class="mb-0 text-success" id="statPaidPayments">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2" style="flex: 0 0 20%; max-width: 20%;">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Overpaid</h6>
                            <h3 class="mb-0 text-primary-custom" id="statOverpaidPayments">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2" style="flex: 0 0 20%; max-width: 20%;">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Balance</h6>
                            <h3 class="mb-0 text-danger" id="statTotalBalance">TZS 0</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amount Summary -->
            <div class="row mb-4" id="amountSummary">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Amount Required</h6>
                            <h3 class="mb-0 text-primary-custom" id="statTotalRequired">TZS 0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Amount Paid</h6>
                            <h3 class="mb-0 text-success" id="statTotalPaid">TZS 0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Outstanding Balance</h6>
                            <h3 class="mb-0 text-danger" id="statOutstandingBalance">TZS 0.00</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-calendar"></i> Academic Year
                            </label>
                            <select class="form-select" id="filterYear">
                                @php
                                    $defaultYear = $defaultYear ?? date('Y');
                                    $availableYears = $availableYears ?? [];
                                @endphp
                                @if(count($availableYears) > 0)
                                    @foreach($availableYears as $academicYear)
                                        @php
                                            $yearValue = is_array($academicYear) ? $academicYear['year'] : $academicYear;
                                            $yearName = is_array($academicYear) ? ($academicYear['year_name'] ?? $academicYear['year']) : $yearValue;
                                            $status = is_array($academicYear) ? ($academicYear['status'] ?? '') : '';
                                            $isSelected = $yearValue == $defaultYear;
                                        @endphp
                                        <option value="{{ $yearValue }}" {{ $isSelected ? 'selected' : '' }}>
                                            {{ $yearName }} @if($status) ({{ $status }}) @endif
                                        </option>
                                    @endforeach
                                @else
                                    <option value="{{ $defaultYear }}" selected>{{ $defaultYear }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-book"></i> Class
                            </label>
                            <select class="form-select" id="filterClass">
                                <option value="">All Classes</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-layers"></i> Subclass
                            </label>
                            <select class="form-select" id="filterSubclass">
                                <option value="">All Subclasses</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-person-check"></i> Status
                            </label>
                            <select class="form-select" id="filterStatus">
                                <option value="">All Statuses</option>
                                <option value="Active">Active</option>
                                <option value="Graduated">Graduated</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-none">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-filter"></i> Fee Type
                            </label>
                            <select class="form-select" id="filterFeeType">
                                <option value="" selected>All Types</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-credit-card"></i> Payment Status
                            </label>
                            <select class="form-select" id="filterPaymentStatus">
                                <option value="">All Statuses</option>
                                <option value="Pending">Pending</option>
                                <option value="Incomplete">Incomplete</option>
                                <option value="Partial">Partial</option>
                                <option value="Paid">Paid</option>
                                <option value="Overpaid">Overpaid</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-shield-check"></i> Sponsorship
                            </label>
                            <select class="form-select" id="filterSponsorship">
                                <option value="">All</option>
                                <option value="sponsored">Sponsored</option>
                                <option value="self">Self-paying</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-search"></i> Search Student Name
                            </label>
                            <input type="text" class="form-control" id="searchStudentName" placeholder="Enter student name...">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-arrow-clockwise"></i> Actions
                            </label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary flex-fill" id="clearFiltersBtn" title="Clear Filters">
                                    <i class="bi bi-x-circle"></i> Clear
                                </button>
                                <button type="button" class="btn btn-primary-custom flex-fill" id="openReportBtn" title="Open Payments Report">
                                    <i class="bi bi-graph-up"></i> Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary-custom text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Control Numbers & Payments List
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
                        <table class="table table-hover table-sm mb-0" id="paymentsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Student Details</th>
                                    <th>Payment Type</th>
                                    <th>Bill Summary (TZS)</th>
                                    <th>Balance & Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tfoot class="table-light">
                                <tr>
                                    <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filter Student" data-column="0"></th>
                                    <th><select class="form-control form-control-sm column-filter" data-column="1">
                                        <option value="">All Types</option>
                                        <option value="Sponsored">Sponsored</option>
                                        <option value="Own Payment">Own Payment</option>
                                    </select></th>
                                    <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filter Bill" data-column="2"></th>
                                    <th><select class="form-control form-control-sm column-filter" data-column="3">
                                        <option value="">All Status</option>
                                        <option value="Paid">Paid</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Incomplete">Incomplete</option>
                                    </select></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                            <tbody id="paymentsTableBody">
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View More Modal -->
<div class="modal fade" id="viewMoreModal" tabindex="-1" aria-labelledby="viewMoreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 95%; width: 1200px;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewMoreModalLabel">
                    <i class="bi bi-eye"></i> Payment Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Student Information -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-2 text-center">
                        <div class="student-photo-modal-container">
                            <img id="view_student_photo" src="" alt="Student Photo" 
                                class="img-thumbnail" 
                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; display: none;"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div id="view_student_photo_placeholder" 
                                class="student-photo-placeholder d-none" 
                                style="width: 100px; height: 100px; border-radius: 50%; display: none; align-items: center; justify-content: center; font-size: 36px; font-weight: bold; color: white; margin: 0 auto;">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4 class="text-primary-custom mb-1" id="view_student_name">-</h4>
                        <p class="mb-0 text-muted"> <i class="bi bi-book"></i> <span id="view_student_class">-</span> | <i class="bi bi-person-badge"></i> <span id="view_student_admission">-</span></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="p-2 bg-light rounded border">
                            <small class="text-muted d-block">Control Number</small>
                            <span class="h5 mb-0 text-primary-custom fw-bold" id="view_control_number">-</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Summary Dashboard -->
                <div class="row mb-4 g-2">
                    <div class="col">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body p-2 text-center">
                                <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Fee Required</small>
                                <h6 class="mb-0 text-primary-custom fw-bold" id="view_total_fee_required">0.00</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body p-2 text-center">
                                <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Total Paid</small>
                                <h6 class="mb-0 text-success fw-bold" id="view_total_paid">0.00</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body p-2 text-center">
                                <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Bill Balance</small>
                                <h6 class="mb-0 text-danger fw-bold" id="view_bill_balance">0.00</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body p-2 text-center">
                                <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Prev. Year Debt</small>
                                <h6 class="mb-0 text-warning fw-bold" id="view_old_debt">0.00</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-0 bg-primary-custom text-white h-100">
                            <div class="card-body p-2 text-center">
                                <small class="d-block mb-1" style="font-size: 0.75rem;">Total Outstanding</small>
                                <h6 class="mb-0 fw-bold" id="view_total_outstanding">0.00</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sponsorship Information (Hidden by default) -->
                <div id="sponsorInfoSection" class="mb-4" style="display: none;">
                    <div class="card border-primary-custom shadow-sm bg-light">
                        <div class="card-header bg-primary-custom text-white py-1 px-3">
                            <small class="fw-bold"><i class="bi bi-shield-check me-2"></i> Sponsorship Information</small>
                        </div>
                        <div class="card-body py-2 px-3">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <h6 class="mb-1 text-primary-custom fw-bold" id="view_sponsor_name">-</h6>
                                    <div class="small mb-1"><i class="bi bi-person"></i> <span id="view_sponsor_contact_person">-</span></div>
                                    <div class="small text-muted mb-1"><i class="bi bi-telephone"></i> <span id="view_sponsor_phone">-</span></div>
                                    <div class="small text-muted"><i class="bi bi-envelope"></i> <span id="view_sponsor_email">-</span></div>
                                </div>
                                <div class="col-md-3 text-center border-start border-end">
                                    <div class="display-6 fw-bold text-primary-custom" id="view_sponsor_percentage">0%</div>
                                    <small class="text-muted d-block text-uppercase" style="font-size: 0.65rem;">Coverage</small>
                                </div>
                                <div class="col-md-4 text-end">
                                    <small class="text-muted d-block">Sponsor Component</small>
                                    <h5 class="mb-0 text-success fw-bold" id="view_sponsor_amount">0.00/=</h5>
                                    <small class="text-muted" style="font-size: 0.7rem;">(Calculated from total fees)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Record Payment Button Section -->
                <div id="modalRecordPaymentContainer" class="mb-4 text-center">
                    <!-- Button will be dynamically inserted here -->
                </div>

                <!-- Expandable Detailed Breakdown -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom-0 p-0">
                        <button class="btn btn-link w-100 text-start text-decoration-none p-3 d-flex justify-content-between align-items-center" 
                                type="button" data-toggle="collapse" data-target="#detailedBreakdownCollapse" aria-expanded="false">
                            <span class="fw-bold text-dark"><i class="bi bi-list-task me-2"></i> Detailed Fee Breakdown</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </div>
                    <div class="collapse" id="detailedBreakdownCollapse">
                        <div class="card-body pt-0">
                            <!-- Tuition Fees Details -->
                            <div id="tuitionFeesSection" style="display: none;">
                                <div id="tuitionFeesContent" class="mb-4">
                                    <!-- Tuition fees will be dynamically inserted here -->
                                </div>
                            </div>

                            <!-- Other Fees Details -->
                            <div id="otherFeesSection" style="display: none;">
                                <div id="otherFeesContent">
                                    <!-- Other fees will be dynamically inserted here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction History -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white border-bottom-0 p-0">
                        <button class="btn btn-link w-100 text-start text-decoration-none p-3 d-flex justify-content-between align-items-center" 
                                type="button" data-toggle="collapse" data-target="#allTransactionsCollapse" aria-expanded="false">
                            <span class="fw-bold text-dark"><i class="bi bi-clock-history me-2"></i> All Transactions of Control Number</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </div>
                    <div class="collapse" id="allTransactionsCollapse">
                        <div class="card-body pt-0">
                            <div id="allTransactionsContent">
                                <!-- Transaction history will be dynamically inserted here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" id="modalExportPdfBtn">
                    <i class="bi bi-file-earmark-pdf"></i> Export PDF
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Update Payment Modal -->
<div class="modal fade" id="updatePaymentModal" tabindex="-1" aria-labelledby="updatePaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="updatePaymentModalLabel">
                    <i class="bi bi-pencil"></i> Update Payment
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="updatePaymentForm">
                @csrf
                <input type="hidden" name="paymentID" id="update_payment_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount Required</label>
                        <input type="text" id="update_amount_required" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount Paid (Current)</label>
                        <input type="text" id="update_amount_paid_current" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Payment Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount_paid" id="update_amount_paid" class="form-control" placeholder="0.00" step="0.01" min="0" required>
                        <small class="text-muted">Enter the new payment amount received</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Reference</label>
                        <input type="text" name="payment_reference" id="update_payment_reference" class="form-control" placeholder="e.g., Bank reference number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes</label>
                        <textarea name="notes" id="update_notes" class="form-control" rows="3" placeholder="Optional notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Update Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="recordPaymentModalLabel">
                    <i class="bi bi-cash-coin"></i> Record Payment
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="recordPaymentForm">
                @csrf
                <input type="hidden" name="paymentID" id="record_payment_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Control Number</label>
                        <input type="text" class="form-control" id="record_control_number" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fee Type</label>
                        <input type="text" class="form-control" id="record_fee_type" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount Required</label>
                        <input type="text" class="form-control" id="record_amount_required" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount Already Paid</label>
                        <input type="text" class="form-control" id="record_amount_paid" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Balance</label>
                        <input type="text" class="form-control" id="record_balance" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_source" id="record_payment_source" class="form-select" required>
                            <option value="Cash">Cash</option>
                            <option value="Bank">Bank</option>
                        </select>
                        <small class="text-muted">Select payment method</small>
                    </div>
                    <div class="mb-3" id="record_bank_name_container" style="display: none;">
                        <label class="form-label fw-bold">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" id="record_bank_name" class="form-control" placeholder="e.g., CRDB Bank, NMB Bank">
                        <small class="text-muted">Enter bank name</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Paid Amount (TZS) <span class="text-danger">*</span></label>
                        <input type="number" name="paid_amount" id="record_paid_amount" class="form-control" placeholder="0.00" step="0.01" min="0.01" required>
                        <small class="text-muted">Enter the amount being paid</small>
                    </div>
                    <div class="mb-3" id="record_reference_number_container">
                        <label class="form-label fw-bold">Reference Number <span class="text-danger" id="record_reference_required">*</span></label>
                        <input type="text" name="reference_number" id="record_reference_number" class="form-control" placeholder="e.g., BANK123456">
                        <small class="text-muted">Unique reference number (required for bank payments)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" id="record_payment_date" class="form-control" required>
                        <small class="text-muted">Date when payment was made</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes</label>
                        <textarea name="notes" id="record_notes" class="form-control" rows="3" placeholder="Optional notes about this payment"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Payment Records Modal -->
<div class="modal fade" id="viewPaymentRecordsModal" tabindex="-1" aria-labelledby="viewPaymentRecordsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewPaymentRecordsModalLabel">
                    <i class="bi bi-eye"></i> Payment Records
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Control Number</label>
                    <input type="text" class="form-control" id="view_records_control_number" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Fee Type</label>
                    <input type="text" class="form-control" id="view_records_fee_type" readonly>
                </div>
                <div id="paymentRecordsContent">
                    <!-- Payment records will be loaded dynamically -->
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary-custom" role="status"></div>
                        <p class="mt-2">Loading payment records...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Payment Record Modal -->
<div class="modal fade" id="editPaymentRecordModal" tabindex="-1" aria-labelledby="editPaymentRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editPaymentRecordModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Payment Record
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editPaymentRecordForm">
                @csrf
                <input type="hidden" name="recordID" id="edit_record_id">
                <input type="hidden" name="paymentID" id="edit_payment_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount Paid (TZS) <span class="text-danger">*</span></label>
                        <input type="number" name="paid_amount" id="edit_paid_amount" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" id="edit_payment_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_source" id="edit_payment_source" class="form-select" required>
                            <option value="Cash">Cash</option>
                            <option value="Bank">Bank</option>
                        </select>
                    </div>
                    <div class="mb-3" id="edit_reference_number_group" style="display: none;">
                        <label class="form-label fw-bold">Reference Number <span class="text-danger">*</span></label>
                        <input type="text" name="reference_number" id="edit_reference_number" class="form-control">
                    </div>
                    <div class="mb-3" id="edit_bank_name_group" style="display: none;">
                        <label class="form-label fw-bold">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" id="edit_bank_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes</label>
                        <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Core dependencies wait and load -->
<script>
(function() {
    var scripts = [
        'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js',
        'https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js',
        'https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js',
        'https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js',
        'https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js',
        'https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js'
    ];

    function loadScript(url, callback) {
        var script = document.createElement('script');
        script.src = url;
        script.onload = callback;
        script.onerror = function() {
            console.error('Failed to load script: ' + url);
            // Continue with next script even if one fails
            if (callback) callback();
        };
        document.head.appendChild(script);
    }

    function loadScriptsSequentially(urls, callback) {
        if (urls.length === 0) {
            if (callback) callback();
            return;
        }
        var nextUrl = urls.shift();
        loadScript(nextUrl, function() {
            loadScriptsSequentially(urls, callback);
        });
    }

    function waitForJQuery(callback) {
        if (window.jQuery) {
            callback();
        } else {
            setTimeout(function() { waitForJQuery(callback); }, 100);
        }
    }

    // Show loading state in table immediately
    waitForJQuery(function() {
        var $ = window.jQuery;
        $('#paymentsTableBody').html('<tr><td colspan="4" class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"></div><p class="mt-2">Initializing assets...</p></td></tr>');
        
        loadScriptsSequentially(scripts, function() {
            console.log('All scripts loaded, initializing payments management...');
            
            // Ensure jsPDF mapping
            if (typeof window.jspdf !== 'undefined' && !window.jsPDF) {
                window.jsPDF = window.jspdf.jsPDF;
            }
            
            initPaymentsApp($);
        });
    });

    function initPaymentsApp($) {
        // Function to generate placeholder color
        function getPlaceholderColor(name) {
            if (!name) return '#940000';
            var colors = ['#940000', '#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6f42c1', '#e83e8c', '#fd7e14', '#20c997', '#6610f2', '#6c757d', '#1abc9c', '#3498db', '#9b59b6', '#e74c3c', '#f39c12', '#16a085', '#2980b9', '#8e44ad'];
            var hash = 0;
            for (var i = 0; i < name.length; i++) {
                hash = name.charCodeAt(i) + ((hash << 5) - hash);
            }
            return colors[Math.abs(hash) % colors.length];
        }

        // Load classes and subclasses on page load
        var classesData = @json($classes ?? []);
        var subclassesData = @json($subclasses ?? []);
        
        // Populate classes dropdown
        if (classesData && classesData.length > 0) {
            classesData.forEach(function(cls) {
                $('#filterClass').append('<option value="' + cls.classID + '">' + cls.class_name + '</option>');
            });
        }
        
        // Update subclasses when class changes
        $('#filterClass').on('change', function() {
            var selectedClassID = $(this).val();
            $('#filterSubclass').html('<option value="">All Subclasses</option>');
            
            if (selectedClassID && subclassesData && subclassesData.length > 0) {
                var filteredSubclasses = subclassesData.filter(function(sub) {
                    return sub.classID == selectedClassID;
                });
                
                filteredSubclasses.forEach(function(sub) {
                    $('#filterSubclass').append('<option value="' + sub.subclassID + '">' + sub.subclass_name + '</option>');
                });
            }
            
            loadPaymentsData();
        });

        // Variable to store the current AJAX request to allow aborting previous ones
        var currentPaymentsAjax = null;

        // Function to load payments data via AJAX
        function loadPaymentsData() {
            // Abort previous request if it exists
            if (currentPaymentsAjax) {
                currentPaymentsAjax.abort();
            }

            var year = $('#filterYear').val();
            var classID = $('#filterClass').val();
            var subclassID = $('#filterSubclass').val();
            var studentStatus = $('#filterStatus').val();
            var paymentStatus = $('#filterPaymentStatus').val();
            var searchStudentName = $('#searchStudentName').val();

            // Clear previous table instance
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#paymentsTable')) {
                $('#paymentsTable').DataTable().destroy();
            }

            // Show loading
            $('#paymentsTableBody').html('<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"></div><p class="mt-2">Loading payments...</p></td></tr>');

            currentPaymentsAjax = $.ajax({
                url: '{{ route("get_payments_ajax") }}',
                type: 'GET',
                data: {
                    year: year,
                    class_id: classID,
                    subclass_id: subclassID,
                    student_status: studentStatus,
                    payment_status: paymentStatus,
                    search_student_name: searchStudentName,
                    sponsorship_filter: $('#filterSponsorship').val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        var isClosedYear = response.is_closed_year || false;
                        var html = '';
                        if (response.data.length > 0) {
                            response.data.forEach(function(item) {
                                var student = item.student;
                                var paymentsData = item.payment || {}; // Renamed to avoid conflict
                                var totals = item.totals || {};
                                
                                // Generate placeholder
                                var firstName = student.first_name || '';
                                var firstLetter = firstName ? firstName.charAt(0).toUpperCase() : 'N';
                                var fullName = (student.first_name || '') + ' ' + (student.last_name || '');
                                var placeholderColor = getPlaceholderColor(fullName);

                                // Photo HTML
                                var photoHtml = '';
                                if (student.photo) {
                                    photoHtml = '<div class="photo-container me-2">' +
                                        '<img src="' + student.photo + '" alt="Photo" class="student-photo" ' +
                                        'onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">' +
                                        '<div class="student-photo-placeholder d-none" style="background-color: ' + placeholderColor + ';">' + firstLetter + '</div>' +
                                        '</div>';
                                } else {
                                    photoHtml = '<div class="photo-container me-2">' +
                                        '<div class="student-photo-placeholder" style="background-color: ' + placeholderColor + ';">' + firstLetter + '</div>' +
                                        '</div>';
                                }

                                var studentFullName = (student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '');
                                var studentClass = 'N/A';
                                if (student.subclass) {
                                    var mainClass = student.subclass.class_name || '';
                                    var subclass = student.subclass.subclass_name || '';
                                    studentClass = (mainClass && subclass) ? mainClass + ' ' + subclass : (subclass || mainClass || 'N/A');
                                }

                                // Column 1: Student Details
                                var col1 = '<div class="d-flex align-items-center">' +
                                    photoHtml +
                                    '<div>' +
                                    '<div class="fw-bold text-dark">' + studentFullName.trim() + '</div>' +
                                    '<div class="small text-muted">' + (student.admission_number || 'No Adm') + ' | ' + studentClass + '</div>' +
                                    '<div class="small badge bg-light text-dark border">' + (item.academic_year || '') + '</div>' +
                                    '</div>' +
                                    '</div>';

                                // Column 2: Payment Type
                                var colType = '';
                                if (student.sponsor) {
                                    colType = '<div class="text-center"><span class="badge bg-primary-custom text-white mb-1" style="font-size: 0.7rem; padding: 4px 8px; display: inline-block; width: 100px;">' +
                                        '<i class="bi bi-shield-check me-1"></i>Sponsored' +
                                        '</span><br><small class="text-muted">' + student.sponsor.percentage + '% Cover</small> <br> <small class="text-muted" style="font-size: 0.65rem;">' + student.sponsor.sponsor_name + '</small></div>';
                                } else {
                                    colType = '<div class="text-center"><span class="badge bg-secondary text-white" style="font-size: 0.7rem; padding: 4px 8px; display: inline-block; width: 100px;">' +
                                        '<i class="bi bi-person me-1"></i>Own Payment' +
                                        '</span></div>';
                                }

                                // Column 3: Bill Summary
                                var col2 = '<div class="bill-container">' +
                                    '<div class="bill-item text-muted small">Required: <span class="fw-bold text-dark">' + parseFloat(totals.total_required || 0).toLocaleString() + '</span></div>' +
                                    '<div class="bill-item text-muted small">Paid: <span class="fw-bold text-success">' + parseFloat(totals.total_paid || 0).toLocaleString() + '</span></div>' +
                                    '</div>';

                                // Column 4: Balance & Status
                                var overallStatus = totals.overall_status || 'Pending';
                                var statusClass = 'bg-secondary';
                                if (overallStatus === 'Paid') statusClass = 'bg-success';
                                else if (overallStatus === 'Overpaid') statusClass = 'bg-info';
                                else if (overallStatus.includes('Incomplete') || overallStatus === 'Partial') statusClass = 'bg-warning text-dark';
                                
                                var col3 = '<div class="text-end">' +
                                    '<div class="fw-bold text-danger mb-1">' + parseFloat(totals.total_balance || 0).toLocaleString() + '/=</div>' +
                                    '<span class="badge ' + statusClass + ' mb-1">' + overallStatus + '</span>';
                                
                                // Can Start Icon
                                if (totals.can_start_school) {
                                    col3 += ' <i class="bi bi-check-circle-fill text-success" title="Can Start School"></i>';
                                }
                                col3 += '</div>';

                                // Column 5: Actions
                                var paymentsJson = JSON.stringify(paymentsData).replace(/'/g, "&#39;");
                                var sponsorJson = student.sponsor ? JSON.stringify(student.sponsor).replace(/'/g, "&#39;") : '';
                                var col4 = '<button class="btn btn-primary-custom btn-sm view-more-btn" ' +
                                    'data-student-id="' + student.studentID + '" ' +
                                    'data-student-name="' + studentFullName.trim() + '" ' +
                                    'data-student-admission="' + (student.admission_number || 'N/A') + '" ' +
                                    'data-student-class="' + studentClass + '" ' +
                                    'data-student-photo="' + (student.photo || '') + '" ' +
                                    'data-student-first-letter="' + firstLetter + '" ' +
                                    'data-student-placeholder-color="' + placeholderColor + '" ' +
                                    'data-student-sponsor=\'' + sponsorJson + '\' ' +
                                    'data-is-closed-year="' + (isClosedYear ? 'true' : 'false') + '" ' +
                                    'data-payment=\'' + paymentsJson + '\' ' +
                                    'data-totals=\'' + JSON.stringify(totals).replace(/'/g, "&#39;") + '\' ' +
                                    'title="View More Details"><i class="bi bi-eye"></i> Details</button>';

                                html += '<tr>' +
                                    '<td>' + col1 + '</td>' +
                                    '<td>' + colType + '</td>' +
                                    '<td class="text-end">' + col2 + '</td>' +
                                    '<td class="text-end">' + col3 + '</td>' +
                                    '<td class="text-center">' + col4 + '</td>' +
                                    '</tr>';
                            });
                        } else {
                            html = '<tr><td colspan="5" class="text-center py-4 text-muted"><p class="mt-2">No payments found</p></td></tr>';
                        }
                        
                        $('#paymentsTableBody').html(html);
                        
                        // Update statistics
                        if (response.statistics) {
                            var stats = response.statistics;
                            $('#statPendingPayments').text(stats.pending_payments || 0);
                            $('#statIncompletePayments').text(stats.incomplete_payments || 0);
                            $('#statPaidPayments').text(stats.paid_payments || 0);
                            $('#statOverpaidPayments').text(stats.overpaid_payments || 0);
                            $('#statTotalBalance').text('TZS ' + parseFloat(stats.total_balance || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
                            $('#statTotalRequired').text('TZS ' + parseFloat(stats.total_required || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
                            $('#statTotalPaid').text('TZS ' + parseFloat(stats.total_paid || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
                            $('#statOutstandingBalance').text('TZS ' + parseFloat(stats.total_balance || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
                            
                            // Log additional statistics for debugging
                            if (stats.total_students !== undefined) {
                                console.log('Filter Statistics:', {
                                    total_students: stats.total_students,
                                    active_students: stats.active_students,
                                    graduated_students: stats.graduated_students,
                                    students_with_debt: stats.students_with_debt,
                                    students_paid: stats.students_paid
                                });
                            }
                        }
                        
                        // Initialize DataTable
                        if ($.fn.DataTable) {
                            // Check again if it's already a DataTable (in case another request finished just before this)
                            if ($.fn.DataTable.isDataTable('#paymentsTable')) {
                                $('#paymentsTable').DataTable().destroy();
                            }
                            
                            var table = $('#paymentsTable').DataTable({
                                destroy: true, // Allow re-initialization by destroying previous instance
                                dom: 'Bfrtip',
                                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                                pageLength: 10,
                                lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
                                order: [[0, 'asc']],
                                responsive: true,
                                scrollX: true,
                                autoWidth: false,
                                columnDefs: [
                                    { width: "30%", targets: 0 },
                                    { width: "15%", targets: 1 },
                                    { width: "20%", targets: 2 },
                                    { width: "20%", targets: 3 },
                                    { width: "15%", targets: 4, orderable: false }
                                ]
                            });

                            // Column filters
                            $('.column-filter').off('keyup change').on('keyup change', function() {
                                var columnIndex = $(this).data('column');
                                table.column(columnIndex).search($(this).val()).draw();
                            });
                        }
                    } else {
                        $('#paymentsTableBody').html('<tr><td colspan="5" class="text-center py-4 text-muted"><p class="mt-2">No payments found</p></td></tr>');
                    }
                },
                error: function(xhr, status, error) {
                    if (status === 'abort') return; // Ignore aborted requests
                    console.error('Error loading payments:', xhr);
                    $('#paymentsTableBody').html('<tr><td colspan="5" class="text-center py-4 text-danger">Error loading payments. Please try again.</td></tr>');
                }
            });
        }

        // Real-time filtering
        $('#filterYear, #filterClass, #filterSubclass, #filterStatus, #filterPaymentStatus, #filterSponsorship').on('change', function() {
            loadPaymentsData();
        });

        // Search student name with debounce
        var searchTimeout;
        $('#searchStudentName').on('keyup', function() {
            clearTimeout(searchTimeout);
            var searchValue = $(this).val();
            searchTimeout = setTimeout(function() {
                loadPaymentsData();
            }, 500); // Wait 500ms after user stops typing
        });

        // Clear all filters
        $('#clearFiltersBtn').on('click', function() {
            $('#filterYear').val('{{ $currentYear }}');
            $('#filterClass').val('');
            $('#filterSubclass').html('<option value="">All Subclasses</option>');
            $('#filterStatus').val('');
            $('#filterPaymentStatus').val('');
            $('#filterSponsorship').val('');
            $('#searchStudentName').val('');
            loadPaymentsData();
        });
        
        // Export filtered PDF
        $('#openReportBtn').on('click', function() {
            var year = $('#filterYear').val();
            var classID = $('#filterClass').val();
            var subclassID = $('#filterSubclass').val();
            var studentStatus = $('#filterStatus').val();
            var paymentStatus = $('#filterPaymentStatus').val();
            var searchStudentName = $('#searchStudentName').val();
            var sponsorshipFilter = $('#filterSponsorship').val();

            var params = new URLSearchParams();
            if (year) params.append('year', year);
            if (classID) params.append('class_id', classID);
            if (subclassID) params.append('subclass_id', subclassID);
            if (studentStatus) params.append('student_status', studentStatus);
            if (paymentStatus) params.append('payment_status', paymentStatus);
            if (searchStudentName) params.append('search_student_name', searchStudentName);
            if (sponsorshipFilter) params.append('sponsorship_filter', sponsorshipFilter);

            var reportUrl = '{{ url('payments/report') }}';
            var finalUrl = reportUrl + (params.toString() ? ('?' + params.toString()) : '');
            window.location.href = finalUrl;
        });

        // Load initial data
        loadPaymentsData();

        // ====================================================================
        // FEE SMS MODAL — shared for "Send Control Numbers" + "Debt Reminders"
        // Same UX as result_management.blade.php smsProgressModal
        // ====================================================================
        let feeIsSendingSms  = false;
        let feeStopSms       = false;
        let feeSmsType       = 'control_number'; // 'control_number' | 'debt_reminder'

        function feeUpdateSelectedCount() {
            const count = $('.fee-parent-checkbox:checked').length;
            $('#feeSelectedCount').text('Wapokeaji: ' + count);
            $('#feeStartSendingSms').prop('disabled', count === 0);
        }

        function openFeeSmsModal(type, year, sponsorshipFilter) {
            feeSmsType = type;

            // Set modal title & description
            if (type === 'debt_reminder') {
                $('#feeSmsModalTitle').text('Tuma Kumbusho la Deni');
                $('#feeSmsModalDesc').text('Orodha ya wazazi/sponsors wenye bakaa ya malipo. Unaweza kuchagua wote au baadhi.');
            } else {
                $('#feeSmsModalTitle').text('Tuma Control Numbers SMS');
                $('#feeSmsModalDesc').text('Orodha ya wazazi/sponsors watakaopokea SMS ya Control Number. Chagua kisha bonyeza "Anza Kutuma".');
            }

            // Reset modal state
            $('#feeSmsLoadingState').removeClass('d-none');
            $('#feeSmsRecipientsSection').addClass('d-none');
            $('#feeSmsProgressArea').addClass('d-none');
            $('#feeSmsProgressBar').css('width', '0%').text('0%').removeClass('bg-info').addClass('bg-success');
            $('#feeSmsDeliverySummary').empty();
            $('#feeSmsProgressText').text('0 / 0');
            $('#feeSelectAllParents').prop('checked', false);
            $('#feeStartSendingSms').prop('disabled', true).html('<i class="bi bi-send"></i> Anza Kutuma SMS');
            $('#feeBtnCancelSms').prop('disabled', false).text('Ghairi');
            $('.fee-parent-checkbox, #feeSelectAllParents').prop('disabled', false);
            feeIsSendingSms = false;
            feeStopSms      = false;

            // Show modal
            $('#feeSmsProgressModal').modal('show');

            // Load recipients from server
            $.ajax({
                url: '{{ route("get_fee_sms_recipients") }}',
                method: 'GET',
                data: {
                    type: type,
                    year: year,
                    sponsorship_filter: sponsorshipFilter || ''
                },
                success: function(response) {
                    $('#feeSmsLoadingState').addClass('d-none');
                    const list = $('#feeSmsRecipientsList');
                    list.empty();

                    if (!response.success || !response.recipients || response.recipients.length === 0) {
                        list.html('<tr><td colspan="4" class="text-center text-muted py-3">Hakuna wapokeaji waliokutikana.</td></tr>');
                        $('#feeSmsRecipientsSection').removeClass('d-none');
                        return;
                    }

                    response.recipients.forEach(function(r, idx) {
                        const hasPhone   = r.phone && r.phone.length > 0;
                        const disabledAt = hasPhone ? '' : 'disabled="disabled"';
                        const checkId    = 'fee_chk_' + r.paymentID + '_' + idx;
                        const statusHtml = hasPhone
                            ? '<span class="fee-status-marker text-muted small">Inasubiri</span>'
                            : '<span class="text-danger small">Hana Simu</span>';

                        list.append(`
                            <tr data-payment-id="${r.paymentID}" data-phone="${r.phone || ''}">
                                <td class="text-center">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input fee-parent-checkbox" id="${checkId}" ${disabledAt}>
                                        <label class="custom-control-label" for="${checkId}">&nbsp;&nbsp;&nbsp;</label>
                                    </div>
                                </td>
                                <td>${r.recipientLabel}</td>
                                <td>${r.phone || '<span class="text-muted">-</span>'}</td>
                                <td class="text-center fee-status-col">${statusHtml}</td>
                            </tr>
                        `);
                    });

                    feeUpdateSelectedCount();
                    $('#feeSmsRecipientsSection').removeClass('d-none');
                },
                error: function(xhr) {
                    $('#feeSmsLoadingState').addClass('d-none');
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Imeshindwa kupakua orodha.';
                    $('#feeSmsRecipientsList').html(`<tr><td colspan="4" class="text-center text-danger py-3">${msg}</td></tr>`);
                    $('#feeSmsRecipientsSection').removeClass('d-none');
                }
            });
        }

        // Select All toggle
        $('#feeSelectAllParents').on('change', function() {
            $('.fee-parent-checkbox:not(:disabled)').prop('checked', $(this).is(':checked'));
            feeUpdateSelectedCount();
        });
        $(document).on('change', '.fee-parent-checkbox', function() {
            feeUpdateSelectedCount();
        });

        // Start Sending — async loop per-row (same as result_management)
        $('#feeStartSendingSms').on('click', async function() {
            const selectedRows = $('.fee-parent-checkbox:checked').closest('tr');
            if (selectedRows.length === 0) return;

            const confirmed = await Swal.fire({
                title: 'Tuma SMS?',
                text: `Unataka kutuma SMS kwa wapokeaji ${selectedRows.length}.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                confirmButtonText: 'Ndiyo, Tuma Sasa',
                cancelButtonText: 'Ghairi'
            });
            if (!confirmed.isConfirmed) return;

            feeIsSendingSms = true;
            feeStopSms      = false;

            $('#feeStartSendingSms').prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Inatuma...');
            $('#feeBtnCancelSms').prop('disabled', true);
            $('.fee-parent-checkbox, #feeSelectAllParents').prop('disabled', true);
            $('#feeSmsProgressArea').removeClass('d-none');

            const total = selectedRows.length;
            let delivered = 0;
            let failed    = 0;

            $('#feeSmsProgressText').text(`0 / ${total}`);
            $('#feeSmsDeliverySummary').empty();

            for (let i = 0; i < total; i++) {
                if (feeStopSms) break;

                const row       = $(selectedRows[i]);
                const statusCol = row.find('.fee-status-col');
                const paymentID = row.data('payment-id');

                // Show spinner on this row
                statusCol.html('<div class="spinner-border spinner-border-sm text-primary" role="status"></div>');

                try {
                    const response = await $.ajax({
                        url: '{{ route("send_single_fee_sms") }}',
                        type: 'POST',
                        data: {
                            paymentID: paymentID,
                            type: feeSmsType,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    if (response.success) {
                        statusCol.html('<i class="bi bi-check-circle-fill text-success" style="font-size:1.2rem;" title="Delivered"></i>');
                        delivered++;
                    } else {
                        statusCol.html('<i class="bi bi-exclamation-circle-fill text-danger" style="font-size:1.2rem;" title="' + (response.message || 'Failed') + '"></i>');
                        failed++;
                    }
                } catch (err) {
                    statusCol.html('<i class="bi bi-exclamation-circle-fill text-danger" style="font-size:1.2rem;" title="Network Error"></i>');
                    failed++;
                }

                const current = delivered + failed;
                const percent = Math.round((current / total) * 100);
                $('#feeSmsProgressBar').css('width', percent + '%').text(percent + '%');
                $('#feeSmsProgressText').text(`${current} / ${total}`);
                $('#feeSmsDeliverySummary').html(
                    `<span class="text-success font-weight-bold">${delivered} Zimetumwa</span> | <span class="text-danger font-weight-bold">${failed} Zimefeli</span>`
                );
            }

            feeIsSendingSms = false;
            $('#feeStartSendingSms').html('<i class="bi bi-check-all"></i> Imekamilika').prop('disabled', true);
            $('#feeBtnCancelSms').prop('disabled', false).text('Funga');

            Swal.fire({
                title: 'SMS Zimekamilika!',
                text: `Zimetumwa: ${delivered}, Zimefeli: ${failed}`,
                icon: delivered > 0 ? 'success' : 'info',
                confirmButtonColor: '#940000'
            });
        });

        // ---------- Button: Send Control Numbers ----------
        $('#sendSMSBtn').off('click').on('click', function(e) {
            e.preventDefault();
            openFeeSmsModal('control_number', $('#filterYear').val(), '');
        });

        // ---------- Button: Send Debt Reminders ----------
        $('#sendDebtRemindersBtn').off('click').on('click', function(e) {
            e.preventDefault();
            openFeeSmsModal('debt_reminder', $('#filterYear').val(), '');
        });

        // Function to generate PDF Invoice using jsPDF
        function generatePaymentInvoicePDF(data) {
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
                var tableWidth = pageWidth - (margin * 2); // Total table width
                var yPos = margin;
                var lineHeight = 7;
                var currentYear = new Date().getFullYear();

                // Load school logo if available
                var logoPromise = Promise.resolve(null);
                if (data.schoolLogo) {
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
                        img.src = data.schoolLogo + (data.schoolLogo.indexOf('?') > -1 ? '&' : '?') + 't=' + Date.now();
                    });
                }

                logoPromise.then(function(logoImg) {
                    // Header Section - Logo centered at top
                    yPos = margin;
                    
                    // Logo (centered)
                    if (logoImg) {
                        try {
                            var logoWidth = 40;
                            var logoHeight = (logoImg.height * logoWidth) / logoImg.width;
                            var logoX = (pageWidth - logoWidth) / 2; // Center horizontally
                            doc.addImage(logoImg, 'PNG', logoX, yPos, logoWidth, logoHeight);
                            yPos += logoHeight + 5;
                        } catch(e) {
                            console.log('Error adding logo:', e);
                        }
                    }
                    
                    // School Name (centered, below logo)
                    doc.setFontSize(16);
                    doc.setTextColor(148, 0, 0); // #940000
                    doc.setFont('helvetica', 'bold');
                    doc.text(data.schoolName || 'School Name', pageWidth / 2, yPos, { align: 'center' });
                    yPos += 10;
                    
                    // Invoice Title
                    doc.setFillColor(148, 0, 0);
                    doc.rect(margin, yPos, pageWidth - (margin * 2), 10, 'F');
                    doc.setTextColor(255, 255, 255);
                    doc.setFontSize(14);
                    doc.setFont('helvetica', 'bold');
                    doc.text('STUDENT FEES PAYMENT INVOICE (' + currentYear + ')', pageWidth / 2, yPos + 6, { align: 'center' });
                    yPos += 15;
                    
                    // Student Information Section with Photo
                    // Load student photo
                    var studentPhotoPromise = Promise.resolve(null);
                    if (data.studentPhoto) {
                        studentPhotoPromise = new Promise(function(resolve) {
                            var studentImg = new Image();
                            studentImg.crossOrigin = 'anonymous';
                            studentImg.onload = function() {
                                resolve(studentImg);
                            };
                            studentImg.onerror = function() {
                                resolve(null);
                            };
                            studentImg.src = data.studentPhoto + (data.studentPhoto.indexOf('?') > -1 ? '&' : '?') + 't=' + Date.now();
                        });
                    }
                    
                    studentPhotoPromise.then(function(studentImg) {
                        // Student Photo (left side)
                        if (studentImg) {
                            try {
                                var photoWidth = 30;
                                var photoHeight = (studentImg.height * photoWidth) / studentImg.width;
                                doc.addImage(studentImg, 'PNG', margin, yPos, photoWidth, photoHeight);
                            } catch(e) {
                                console.log('Error adding student photo:', e);
                            }
                        }
                        
                        // Student Name and Class (next to photo)
                        var textX = margin + (studentImg ? 35 : 0);
                        doc.setFontSize(12);
                    doc.setFont('helvetica', 'bold');
                    doc.setTextColor(148, 0, 0);
                        doc.text(data.studentName || 'Student Name', textX, yPos + 5);
                    
                        doc.setFontSize(10);
                    doc.setFont('helvetica', 'normal');
                    doc.setTextColor(0, 0, 0);
                    if (data.studentClass) {
                            doc.text('Class: ' + data.studentClass, textX, yPos + 10);
                        }
                        
                        yPos += (studentImg ? Math.max(30, 15) : 15);
                        
                        // Payment Summary Section
                    if (data.payments && data.payments.length > 0) {
                        var tuitionPayments = data.payments.filter(function(p) { return p.is_required; });
                            var otherPayments = data.payments.filter(function(p) { return !p.is_required; });
                            
                            // Calculate totals from payments array - always calculate to ensure accuracy (including debt)
                            var tuitionBaseRequired = tuitionPayments.reduce(function(sum, p) { 
                                return sum + parseFloat(p.amount_required || p.amountRequired || 0); 
                            }, 0);
                            var tuitionDebt = tuitionPayments.reduce(function(sum, p) { 
                                return sum + parseFloat(p.debt || 0); 
                            }, 0);
                            var tuitionRequired = tuitionPayments.reduce(function(sum, p) { 
                                return sum + parseFloat(p.total_required || (p.amount_required || p.amountRequired || 0) + (p.debt || 0)); 
                            }, 0);
                            
                            var tuitionPaid = tuitionPayments.reduce(function(sum, p) { 
                                var paid = parseFloat(p.amount_paid || p.amountPaid || 0);
                                // If amount_paid is 0, try payment_records
                                if (paid === 0 && p.payment_records && p.payment_records.length > 0) {
                                    paid = p.payment_records.reduce(function(prSum, pr) { 
                                        return prSum + parseFloat(pr.paid_amount || 0); 
                                    }, 0);
                                }
                                return sum + paid;
                            }, 0);
                            
                            var tuitionBalance = tuitionPayments.reduce(function(sum, p) { 
                                var bal = parseFloat(p.balance || 0);
                                if (bal === 0) {
                                    var req = parseFloat(p.amount_required || p.amountRequired || 0);
                                    var paid = parseFloat(p.amount_paid || p.amountPaid || 0);
                                    if (paid === 0 && p.payment_records && p.payment_records.length > 0) {
                                        paid = p.payment_records.reduce(function(prSum, pr) { 
                                            return prSum + parseFloat(pr.paid_amount || 0); 
                                        }, 0);
                                    }
                                    bal = req - paid;
                                }
                                return sum + bal;
                            }, 0);
                            
                            var otherBaseRequired = otherPayments.reduce(function(sum, p) { 
                                return sum + parseFloat(p.amount_required || p.amountRequired || 0); 
                            }, 0);
                            var otherDebt = otherPayments.reduce(function(sum, p) { 
                                return sum + parseFloat(p.debt || 0); 
                            }, 0);
                            var otherRequired = otherPayments.reduce(function(sum, p) { 
                                return sum + parseFloat(p.total_required || (p.amount_required || p.amountRequired || 0) + (p.debt || 0)); 
                            }, 0);
                            
                            var otherPaid = otherPayments.reduce(function(sum, p) { 
                                var paid = parseFloat(p.amount_paid || p.amountPaid || 0);
                                // If amount_paid is 0, try payment_records
                                if (paid === 0 && p.payment_records && p.payment_records.length > 0) {
                                    paid = p.payment_records.reduce(function(prSum, pr) { 
                                        return prSum + parseFloat(pr.paid_amount || 0); 
                                    }, 0);
                                }
                                return sum + paid;
                            }, 0);
                            
                            var otherBalance = otherPayments.reduce(function(sum, p) { 
                                var bal = parseFloat(p.balance || 0);
                                if (bal === 0) {
                                    var req = parseFloat(p.amount_required || p.amountRequired || 0);
                                    var paid = parseFloat(p.amount_paid || p.amountPaid || 0);
                                    if (paid === 0 && p.payment_records && p.payment_records.length > 0) {
                                        paid = p.payment_records.reduce(function(prSum, pr) { 
                                            return prSum + parseFloat(pr.paid_amount || 0); 
                                        }, 0);
                                    }
                                    bal = req - paid;
                                }
                                return sum + bal;
                            }, 0);
                            
                            // Use data object totals if available and more accurate
                            if (data.tuitionPaid !== undefined && data.tuitionPaid !== null && parseFloat(data.tuitionPaid) > 0) {
                                tuitionPaid = parseFloat(data.tuitionPaid);
                            }
                            if (data.otherPaid !== undefined && data.otherPaid !== null && parseFloat(data.otherPaid) > 0) {
                                otherPaid = parseFloat(data.otherPaid);
                            }
                            if (data.tuitionBaseRequired !== undefined && data.tuitionBaseRequired !== null && parseFloat(data.tuitionBaseRequired) > 0) {
                                tuitionBaseRequired = parseFloat(data.tuitionBaseRequired);
                            }
                            if (data.tuitionDebt !== undefined && data.tuitionDebt !== null) {
                                tuitionDebt = parseFloat(data.tuitionDebt);
                            }
                            if (data.tuitionRequired !== undefined && data.tuitionRequired !== null && parseFloat(data.tuitionRequired) > 0) {
                                tuitionRequired = parseFloat(data.tuitionRequired);
                            }
                            if (data.otherBaseRequired !== undefined && data.otherBaseRequired !== null && parseFloat(data.otherBaseRequired) > 0) {
                                otherBaseRequired = parseFloat(data.otherBaseRequired);
                            }
                            if (data.otherDebt !== undefined && data.otherDebt !== null) {
                                otherDebt = parseFloat(data.otherDebt);
                            }
                             if (data.otherRequired !== undefined && data.otherRequired !== null && parseFloat(data.otherRequired) > 0) {
                                 otherRequired = parseFloat(data.otherRequired);
                             }
                             
                             // Add Control Number at the top of the invoice
                             if (data.payments && data.payments.length > 0) {
                                 doc.setFontSize(10);
                                 doc.setFont('helvetica', 'bold');
                                 doc.setTextColor(0, 0, 0);
                                 var controlNo = data.controlNumber || (data.payments[0] && data.payments[0].control_number) || 'N/A';
                                 doc.text('Control Number: ' + controlNo, pageWidth - margin, yPos + 10, { align: 'right' });
                             }
                            
                            // Recalculate balances (using total_required which includes debt)
                            tuitionBalance = tuitionRequired - tuitionPaid;
                            otherBalance = otherRequired - otherPaid;
                            
                            // Calculate total debt and total base required
                            var totalBaseRequired = tuitionBaseRequired + otherBaseRequired;
                            var totalDebt = tuitionDebt + otherDebt;
                            
                            // Payment Summary - Use totals from data if available, otherwise use calculated
                            // Prioritize data from data object (passed from modal) over calculated values
                            var overallRequired = 0;
                            var overallPaid = 0;
                            var overallBalance = 0;
                            
                            if (data.totalRequired !== undefined && data.totalRequired !== null) {
                                overallRequired = parseFloat(data.totalRequired);
                            } else {
                                overallRequired = tuitionRequired + otherRequired;
                            }
                            
                            if (data.totalPaid !== undefined && data.totalPaid !== null) {
                                overallPaid = parseFloat(data.totalPaid);
                            } else {
                                overallPaid = tuitionPaid + otherPaid;
                            }
                            
                            if (data.totalBalance !== undefined && data.totalBalance !== null) {
                                overallBalance = parseFloat(data.totalBalance);
                            } else {
                                overallBalance = tuitionBalance + otherBalance;
                            }
                            
                            // Debug: Log values to console
                            console.log('PDF Summary - overallRequired:', overallRequired, 'overallPaid:', overallPaid, 'overallBalance:', overallBalance);
                            console.log('PDF Summary - data.totalPaid:', data.totalPaid, 'data.tuitionPaid:', data.tuitionPaid, 'data.otherPaid:', data.otherPaid);
                            console.log('PDF Summary - calculated tuitionPaid:', tuitionPaid, 'otherPaid:', otherPaid);
                            
                            // Determine overall status
                            var overallStatus = 'Pending';
                            if (overallBalance <= 0 && overallPaid > 0) {
                                overallStatus = overallPaid > overallRequired ? 'Overpaid' : 'Paid';
                            } else if (overallPaid > 0 && overallBalance > 0) {
                                overallStatus = 'Incomplete Payment';
                            } else if (overallPaid > 0 && overallPaid < overallRequired) {
                                overallStatus = 'Partial';
                            }
                            
                            doc.setFontSize(12);
                            doc.setFont('helvetica', 'bold');
                            doc.setTextColor(148, 0, 0);
                            doc.text('Payment Summary', pageWidth / 2, yPos, { align: 'center' });
                    yPos += 7;
                    
                            doc.autoTable({
                                head: [['Required Amount (TZS)', 'Debt (TZS)', 'Total Required (TZS)', 'Paid Amount (TZS)', 'Bill Balance (TZS)', 'Status']],
                                body: [[
                                    parseFloat(totalBaseRequired).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                    parseFloat(totalDebt).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                    parseFloat(overallRequired).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                    parseFloat(overallPaid).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                    parseFloat(overallBalance).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                    overallStatus
                                ]],
                                startY: yPos,
                                tableWidth: tableWidth,
                                styles: { 
                                    fontSize: 8,
                                    cellPadding: 2,
                                    lineColor: [0, 0, 0],
                                    lineWidth: 0.1,
                                    overflow: 'linebreak',
                                    cellWidth: 'wrap'
                                },
                                headStyles: {
                                    fillColor: [148, 0, 0],
                                    textColor: 255,
                                    fontStyle: 'bold',
                                    fontSize: 8,
                                    halign: 'center'
                                },
                                columnStyles: {
                                    0: { halign: 'right', cellWidth: tableWidth * 0.18, fontStyle: 'bold' },
                                    1: { halign: 'right', cellWidth: tableWidth * 0.15, textColor: [255, 193, 7], fontStyle: 'bold' },
                                    2: { halign: 'right', cellWidth: tableWidth * 0.18, fontStyle: 'bold' },
                                    3: { halign: 'right', cellWidth: tableWidth * 0.18, textColor: [40, 167, 69], fontStyle: 'bold' },
                                    4: { halign: 'right', cellWidth: tableWidth * 0.18, textColor: [220, 53, 69], fontStyle: 'bold' },
                                    5: { cellWidth: tableWidth * 0.13, halign: 'center', fontStyle: 'bold', fontSize: 7 }
                                },
                                margin: { left: margin, right: margin },
                                theme: 'grid'
                            });
                            
                            yPos = doc.lastAutoTable.finalY + 15;
                            
                            // Tuition Fees Table
                        if (tuitionPayments.length > 0) {
                                // Check if need new page
                                if (yPos > pageHeight - 50) {
                                    doc.addPage();
                                    yPos = margin;
                                }
                                
                                // Table with title row
                                var tuitionTableData = [];
                            tuitionPayments.forEach(function(payment) {
                                    // Debug: Log payment object
                                    console.log('Tuition Payment object:', payment);
                                    
                                    // Ensure amount_paid is properly parsed - check multiple possible field names
                                    var amountPaid = 0;
                                    if (payment.amount_paid !== undefined && payment.amount_paid !== null) {
                                        amountPaid = parseFloat(payment.amount_paid);
                                    } else if (payment.amountPaid !== undefined && payment.amountPaid !== null) {
                                        amountPaid = parseFloat(payment.amountPaid);
                                    }
                                    
                                    // If still 0, try to calculate from payment records if available
                                    if (amountPaid === 0 && payment.payment_records && payment.payment_records.length > 0) {
                                        amountPaid = payment.payment_records.reduce(function(sum, pr) { return sum + parseFloat(pr.paid_amount || 0); }, 0);
                                    }
                                    
                                    var baseAmount = parseFloat(payment.amount_required || payment.amountRequired || 0);
                                    var debt = parseFloat(payment.debt || 0);
                                    var totalRequired = parseFloat(payment.total_required || baseAmount + debt);
                                    var balance = parseFloat(payment.balance || 0);
                                    
                                    // Recalculate balance if needed
                                    if (balance === 0 && totalRequired > 0) {
                                        balance = totalRequired - amountPaid;
                                    }
                                    
                                    console.log('Tuition Payment - baseAmount:', baseAmount, 'debt:', debt, 'totalRequired:', totalRequired, 'amountPaid:', amountPaid, 'balance:', balance);
                                    
                                    tuitionTableData.push([
                                        p.fee_name || 'Mandatory Fee',
                                        totalRequired.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                        debt.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                        totalRequired.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                        amountPaid.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                        balance.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/='
                                    ]);
                                });
                                
                                // Title as caption (centered above table)
                                doc.setTextColor(148, 0, 0);
                                doc.setFontSize(12);
                                doc.setFont('helvetica', 'bold');
                                doc.text('School Fee', pageWidth / 2, yPos, { align: 'center' });
                    yPos += 7;
                    
                                doc.autoTable({
                                    head: [['Fee Name', 'Required Amount (TZS)', 'Debt (TZS)', 'Total Required (TZS)', 'Paid Amount (TZS)', 'Bill Balance (TZS)']],
                                    body: tuitionTableData,
                                    startY: yPos,
                                    tableWidth: tableWidth,
                                    styles: { 
                                        fontSize: 8,
                                        cellPadding: 2,
                                        lineColor: [0, 0, 0],
                                        lineWidth: 0.1,
                                        overflow: 'linebreak',
                                        cellWidth: 'wrap'
                                    },
                                    headStyles: {
                                        fillColor: [148, 0, 0],
                                        textColor: 255,
                                        fontStyle: 'bold',
                                        fontSize: 8,
                                        halign: 'center'
                                    },
                                    columnStyles: {
                                        0: { cellWidth: tableWidth * 0.18, halign: 'center', fontSize: 7 },
                                        1: { halign: 'right', cellWidth: tableWidth * 0.16, fontSize: 7 },
                                        2: { halign: 'right', cellWidth: tableWidth * 0.14, textColor: [255, 193, 7], fontSize: 7 },
                                        3: { halign: 'right', cellWidth: tableWidth * 0.16, fontStyle: 'bold', fontSize: 7 },
                                        4: { halign: 'right', cellWidth: tableWidth * 0.16, textColor: [40, 167, 69], fontSize: 7 },
                                        5: { halign: 'right', cellWidth: tableWidth * 0.16, textColor: [220, 53, 69], fontSize: 7 }
                                    },
                                    margin: { left: margin, right: margin },
                                    theme: 'grid'
                                });
                                
                                yPos = doc.lastAutoTable.finalY + 10;
                            }
                            
                            // Other Fees Table
                        if (otherPayments.length > 0) {
                                // Check if need new page
                                if (yPos > pageHeight - 50) {
                                doc.addPage();
                                yPos = margin;
                            }
                            
                                // Table with title row
                                var otherTableData = [];
                            otherPayments.forEach(function(payment) {
                                    // Debug: Log payment object
                                    console.log('Other Payment object:', payment);
                                    
                                    // Ensure amount_paid is properly parsed - check multiple possible field names
                                    var amountPaid = 0;
                                    if (payment.amount_paid !== undefined && payment.amount_paid !== null) {
                                        amountPaid = parseFloat(payment.amount_paid);
                                    } else if (payment.amountPaid !== undefined && payment.amountPaid !== null) {
                                        amountPaid = parseFloat(payment.amountPaid);
                                    }
                                    
                                    // If still 0, try to calculate from payment records if available
                                    if (amountPaid === 0 && payment.payment_records && payment.payment_records.length > 0) {
                                        amountPaid = payment.payment_records.reduce(function(sum, pr) { return sum + parseFloat(pr.paid_amount || 0); }, 0);
                                    }
                                    
                                    var baseAmount = parseFloat(payment.amount_required || payment.amountRequired || 0);
                                    var debt = parseFloat(payment.debt || 0);
                                    var totalRequired = parseFloat(payment.total_required || baseAmount + debt);
                                    var balance = parseFloat(payment.balance || 0);
                                    
                                    // Recalculate balance if needed
                                    if (balance === 0 && totalRequired > 0) {
                                        balance = totalRequired - amountPaid;
                                    }
                                    
                                    console.log('Other Payment - baseAmount:', baseAmount, 'debt:', debt, 'totalRequired:', totalRequired, 'amountPaid:', amountPaid, 'balance:', balance);
                                    
                                    otherTableData.push([
                                        p.fee_name || 'Contribution',
                                        totalRequired.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                        debt.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                        totalRequired.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                        amountPaid.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                        balance.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/='
                                    ]);
                                });
                                
                                // Title as caption (centered above table)
                            doc.setTextColor(148, 0, 0);
                                doc.setFontSize(12);
                                doc.setFont('helvetica', 'bold');
                                doc.text('Other Contribution', pageWidth / 2, yPos, { align: 'center' });
                                yPos += 7;
                                
                                doc.autoTable({
                                    head: [['Fee Name', 'Required Amount (TZS)', 'Debt (TZS)', 'Total Required (TZS)', 'Paid Amount (TZS)', 'Bill Balance (TZS)']],
                                    body: otherTableData,
                                    startY: yPos,
                                    tableWidth: tableWidth,
                                    styles: { 
                                        fontSize: 8,
                                        cellPadding: 2,
                                        lineColor: [0, 0, 0],
                                        lineWidth: 0.1,
                                        overflow: 'linebreak',
                                        cellWidth: 'wrap'
                                    },
                                    headStyles: {
                                        fillColor: [148, 0, 0],
                                        textColor: 255,
                                        fontStyle: 'bold',
                                        fontSize: 8,
                                        halign: 'center'
                                    },
                                    columnStyles: {
                                        0: { cellWidth: tableWidth * 0.18, halign: 'center', fontSize: 7 },
                                        1: { halign: 'right', cellWidth: tableWidth * 0.16, fontSize: 7 },
                                        2: { halign: 'right', cellWidth: tableWidth * 0.14, textColor: [255, 193, 7], fontSize: 7 },
                                        3: { halign: 'right', cellWidth: tableWidth * 0.16, fontStyle: 'bold', fontSize: 7 },
                                        4: { halign: 'right', cellWidth: tableWidth * 0.16, textColor: [40, 167, 69], fontSize: 7 },
                                        5: { halign: 'right', cellWidth: tableWidth * 0.16, textColor: [220, 53, 69], fontSize: 7 }
                                    },
                                    margin: { left: margin, right: margin },
                                    theme: 'grid'
                                });
                                
                                yPos = doc.lastAutoTable.finalY + 10;
                        }

                        // NEW: Transaction History Table
                        if (data.paymentRecords && data.paymentRecords.length > 0) {
                            var historyTableData = [];
                            data.paymentRecords.sort((a, b) => new Date(b.payment_date) - new Date(a.payment_date)).forEach(function(record) {
                                historyTableData.push([
                                    record.payment_date || 'N/A',
                                    parseFloat(record.paid_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                    record.payment_source || 'N/A',
                                    record.reference_number || '-',
                                    record.is_verified ? 'Verified' : 'Unverified'
                                ]);
                            });

                            doc.setTextColor(148, 0, 0);
                            doc.setFontSize(12);
                            doc.setFont('helvetica', 'bold');
                            doc.text('Transaction History', pageWidth / 2, yPos, { align: 'center' });
                            yPos += 7;

                            doc.autoTable({
                                head: [['Date', 'Amount (TZS)', 'Method', 'Reference', 'Status']],
                                body: historyTableData,
                                startY: yPos,
                                tableWidth: tableWidth,
                                styles: { fontSize: 8, cellPadding: 2, lineColor: [0, 0, 0], lineWidth: 0.1 },
                                headStyles: { fillColor: [148, 0, 0], textColor: 255, fontStyle: 'bold', halign: 'center' },
                                columnStyles: {
                                    0: { halign: 'center' },
                                    1: { halign: 'right' },
                                    2: { halign: 'center' },
                                    3: { halign: 'center' },
                                    4: { halign: 'center' }
                                },
                                margin: { left: margin, right: margin },
                                theme: 'grid'
                            });

                            yPos = doc.lastAutoTable.finalY + 10;
                        }
                    }
                    
                    // Footer
                    var totalPages = doc.internal.getNumberOfPages();
                    for (var i = 1; i <= totalPages; i++) {
                        doc.setPage(i);
                        doc.setFontSize(8);
                        doc.setFont('helvetica', 'italic');
                        doc.setTextColor(100, 100, 100);
                        doc.text('Generated on: ' + new Date().toLocaleDateString('en-GB') + ' ' + new Date().toLocaleTimeString('en-GB', {hour: '2-digit', minute: '2-digit'}), pageWidth / 2, pageHeight - 10, { align: 'center' });
                        doc.setFont('helvetica', 'bold');
                        doc.setTextColor(148, 0, 0);
                        doc.text('Powered by: EmCa Technologies LTD', pageWidth / 2, pageHeight - 5, { align: 'center' });
                    }
                    
                    // Save PDF
                        var filename = 'Payment_Invoice_' + (data.studentName || 'student').replace(/\s+/g, '_') + '_' + currentYear + '.pdf';
                        doc.save(filename);
                        
                        Swal.close();
                    });
                    
                    // Footer
                    var totalPages = doc.internal.getNumberOfPages();
                    for (var i = 1; i <= totalPages; i++) {
                        doc.setPage(i);
                        doc.setFontSize(8);
                        doc.setFont('helvetica', 'italic');
                        doc.setTextColor(100, 100, 100);
                        doc.text('Generated on: ' + new Date().toLocaleDateString('en-GB') + ' ' + new Date().toLocaleTimeString('en-GB', {hour: '2-digit', minute: '2-digit'}), pageWidth / 2, pageHeight - 10, { align: 'center' });
                        doc.setFont('helvetica', 'bold');
                        doc.setTextColor(148, 0, 0);
                        doc.text('Powered by: EmCa Technologies LTD', pageWidth / 2, pageHeight - 5, { align: 'center' });
                    }
                    
                    // Save PDF
                    var filename = 'Payment_Invoice_' + (data.studentName || 'student').replace(/\s+/g, '_') + '_' + currentYear + '.pdf';
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
        }
        
        // Helper function to format currency
        function formatCurrency(amount) {
            return parseFloat(amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        // Export PDF Invoice
        $(document).on('click', '#exportInvoiceBtn', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Export Payment Invoice',
                text: 'Please select a student from the table and click "View More", then use the export option.',
                icon: 'info',
                confirmButtonColor: '#940000'
            });
        });

        // Add export button to view more modal
        $(document).on('click', '.view-more-btn', function(e) {
            // Store student data for export
            setTimeout(function() {
                if ($('#viewMoreModal .btn-export-pdf').length === 0) {
                    const exportBtn = '<button type="button" class="btn btn-primary-custom btn-export-pdf">' +
                        '<i class="bi bi-file-earmark-pdf"></i> Export PDF Invoice' +
                        '</button>';
                    $('#viewMoreModal .modal-footer').prepend(exportBtn);
                }
            }, 100);
        });

        // Export PDF from modal
        $(document).on('click', '.btn-export-pdf', function(e) {
            e.preventDefault();
            
            // Get data from modal
            const studentName = $('#view_student_name').text();
            const studentClass = $('#view_student_class').text();
            
            // Get student photo
            const studentPhotoImg = $('#view_student_photo');
            const studentPhotoPlaceholder = $('#view_student_photo_placeholder');
            let studentPhoto = '';
            let studentPhotoFirstLetter = '';
            let studentPhotoPlaceholderColor = '#940000';
            
            if (studentPhotoImg.is(':visible') && studentPhotoImg.attr('src')) {
                studentPhoto = studentPhotoImg.attr('src');
            } else if (studentPhotoPlaceholder.is(':visible')) {
                studentPhotoFirstLetter = studentPhotoPlaceholder.text();
                studentPhotoPlaceholderColor = studentPhotoPlaceholder.css('background-color') || '#940000';
            }
            
            // Get payments and totals from view-more-btn data - use the button that opened this modal
            const viewBtn = $('#viewMoreModal').data('view-more-btn');
            let payments = [];
            let totals = {};
            
            try {
                // Try to get from stored button reference
                if (viewBtn && viewBtn.length > 0) {
                    const paymentsData = viewBtn.data('payments');
                    const totalsData = viewBtn.data('totals');
                    if (paymentsData) {
                        payments = typeof paymentsData === 'string' ? JSON.parse(paymentsData) : paymentsData;
                    }
                    if (totalsData) {
                        totals = typeof totalsData === 'string' ? JSON.parse(totalsData) : totalsData;
                    }
                } else {
                    // Fallback: get from first view-more-btn
                    const firstBtn = $('.view-more-btn').first();
                    const paymentsData = firstBtn.data('payments');
                    const totalsData = firstBtn.data('totals');
                    if (paymentsData) {
                        payments = typeof paymentsData === 'string' ? JSON.parse(paymentsData) : paymentsData;
                    }
                    if (totalsData) {
                        totals = typeof totalsData === 'string' ? JSON.parse(totalsData) : totalsData;
                    }
                }
                
                // Extract payments data from modal tables if available (more accurate)
                const tuitionTable = $('#tuitionFeesContent table tbody tr');
                const otherFeesTable = $('#otherFeesContent table tbody tr');
                
                // Re-extract payments from modal tables for accuracy
                if (tuitionTable.length > 0 || otherFeesTable.length > 0) {
                    payments = [];
                    
                    // Extract from tuition fees table (with Debt and Total Required columns)
                    tuitionTable.each(function() {
                        const row = $(this);
                        const controlNumber = row.find('.control-number').text() || row.find('td').eq(0).text().trim();
                        // Column order: Control Number (0), Required Amount (1), Debt (2), Total Required (3), Paid Amount (4), Bill Balance (5)
                        const requiredText = row.find('td').eq(1).text().replace(/[^\d.]/g, '');
                        const debtText = row.find('td').eq(2).text().replace(/[^\d.]/g, '');
                        const totalRequiredText = row.find('td').eq(3).text().replace(/[^\d.]/g, '');
                        const paidText = row.find('td').eq(4).text().replace(/[^\d.]/g, '');
                        const balanceText = row.find('td').eq(5).text().replace(/[^\d.]/g, '');
                        
                        if (controlNumber && controlNumber !== 'N/A' && controlNumber !== '') {
                            payments.push({
                                fee_type: 'Tuition Fees',
                                control_number: controlNumber,
                                amount_required: parseFloat(requiredText) || 0,
                                debt: parseFloat(debtText) || 0,
                                total_required: parseFloat(totalRequiredText) || (parseFloat(requiredText) || 0) + (parseFloat(debtText) || 0),
                                amount_paid: parseFloat(paidText) || 0,
                                balance: parseFloat(balanceText) || 0
                            });
                        }
                    });
                    
                    // Extract from other fees table (with Debt and Total Required columns)
                    otherFeesTable.each(function() {
                        const row = $(this);
                        const controlNumber = row.find('.control-number').text() || row.find('td').eq(0).text().trim();
                        // Column order: Control Number (0), Required Amount (1), Debt (2), Total Required (3), Paid Amount (4), Bill Balance (5)
                        const requiredText = row.find('td').eq(1).text().replace(/[^\d.]/g, '');
                        const debtText = row.find('td').eq(2).text().replace(/[^\d.]/g, '');
                        const totalRequiredText = row.find('td').eq(3).text().replace(/[^\d.]/g, '');
                        const paidText = row.find('td').eq(4).text().replace(/[^\d.]/g, '');
                        const balanceText = row.find('td').eq(5).text().replace(/[^\d.]/g, '');
                        
                        if (controlNumber && controlNumber !== 'N/A' && controlNumber !== '') {
                            payments.push({
                                fee_type: 'Other Fees',
                                control_number: controlNumber,
                                amount_required: parseFloat(requiredText) || 0,
                                debt: parseFloat(debtText) || 0,
                                total_required: parseFloat(totalRequiredText) || (parseFloat(requiredText) || 0) + (parseFloat(debtText) || 0),
                                amount_paid: parseFloat(paidText) || 0,
                                balance: parseFloat(balanceText) || 0
                            });
                        }
                    });
                    
                    // Recalculate totals from extracted payments (including debt and total_required)
                    const tuitionPayments = payments.filter(p => p.fee_type === 'Tuition Fees');
                    const otherFeePayments = payments.filter(p => p.fee_type === 'Other Fees');
                    
                    const tuitionBaseRequired = tuitionPayments.reduce((sum, p) => sum + (p.amount_required || 0), 0);
                    const tuitionDebt = tuitionPayments.reduce((sum, p) => sum + (p.debt || 0), 0);
                    const tuitionTotalRequired = tuitionPayments.reduce((sum, p) => sum + (p.total_required || (p.amount_required || 0) + (p.debt || 0)), 0);
                    
                    const otherBaseRequired = otherFeePayments.reduce((sum, p) => sum + (p.amount_required || 0), 0);
                    const otherDebt = otherFeePayments.reduce((sum, p) => sum + (p.debt || 0), 0);
                    const otherTotalRequired = otherFeePayments.reduce((sum, p) => sum + (p.total_required || (p.amount_required || 0) + (p.debt || 0)), 0);
                    
                    totals = {
                        tuition_base_required: tuitionBaseRequired,
                        tuition_debt: tuitionDebt,
                        tuition_required: tuitionTotalRequired,
                        tuition_paid: tuitionPayments.reduce((sum, p) => sum + (p.amount_paid || 0), 0),
                        tuition_balance: tuitionPayments.reduce((sum, p) => sum + (p.balance || 0), 0),
                        other_base_required: otherBaseRequired,
                        other_debt: otherDebt,
                        other_required: otherTotalRequired,
                        other_paid: otherFeePayments.reduce((sum, p) => sum + (p.amount_paid || 0), 0),
                        other_balance: otherFeePayments.reduce((sum, p) => sum + (p.balance || 0), 0),
                        total_base_required: tuitionBaseRequired + otherBaseRequired,
                        total_debt: tuitionDebt + otherDebt,
                        total_required: tuitionTotalRequired + otherTotalRequired,
                        total_paid: (tuitionPayments.reduce((sum, p) => sum + (p.amount_paid || 0), 0) + 
                                    otherFeePayments.reduce((sum, p) => sum + (p.amount_paid || 0), 0)),
                        total_balance: (tuitionPayments.reduce((sum, p) => sum + (p.balance || 0), 0) + 
                                       otherFeePayments.reduce((sum, p) => sum + (p.balance || 0), 0))
                    };
                }
                
                console.log('PDF Export - Payments:', payments);
                console.log('PDF Export - Totals:', totals);
            } catch(e) {
                console.error('Error parsing payments data:', e);
            }
            
            // Get school info - we'll need to fetch this or get from page
            // For now, we'll use placeholders or fetch from server
            Swal.fire({
                title: 'Generating PDF...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Fetch school info via AJAX
            $.ajax({
                url: '{{ route("get_school_details") ?? "/get_school_details" }}',
                method: 'GET',
                dataType: 'json',
                success: function(schoolResponse) {
                    const school = schoolResponse.school || {};
                    
                    const pdfData = {
                        studentName: studentName,
                        studentClass: studentClass,
                        studentPhoto: studentPhoto,
                        studentPhotoFirstLetter: studentPhotoFirstLetter,
                        studentPhotoPlaceholderColor: studentPhotoPlaceholderColor,
                        payments: payments,
                        tuitionBaseRequired: totals.tuition_base_required || 0,
                        tuitionDebt: totals.tuition_debt || 0,
                        tuitionRequired: totals.tuition_required || 0,
                        tuitionPaid: totals.tuition_paid || 0,
                        tuitionBalance: totals.tuition_balance || 0,
                        otherBaseRequired: totals.other_base_required || 0,
                        otherDebt: totals.other_debt || 0,
                        otherRequired: totals.other_required || 0,
                        otherPaid: totals.other_paid || 0,
                        otherBalance: totals.other_balance || 0,
                        totalBaseRequired: totals.total_base_required || 0,
                        totalDebt: totals.total_debt || 0,
                        totalRequired: totals.total_required || 0,
                        totalPaid: totals.total_paid || 0,
                        totalBalance: totals.total_balance || 0,
                        schoolName: school.school_name || 'School Name',
                        schoolReg: school.registration_number || '',
                        schoolPhone: school.phone || '',
                        schoolEmail: school.email || '',
                        schoolLogo: school.school_logo || null
                    };
                    
                    generatePaymentInvoicePDF(pdfData);
                },
                error: function() {
                    // Use default values if school fetch fails
                    const pdfData = {
                        studentName: studentName,
                        studentClass: studentClass,
                        studentPhoto: studentPhoto,
                        studentPhotoFirstLetter: studentPhotoFirstLetter,
                        studentPhotoPlaceholderColor: studentPhotoPlaceholderColor,
                        payments: payments,
                        tuitionBaseRequired: totals.tuition_base_required || 0,
                        tuitionDebt: totals.tuition_debt || 0,
                        tuitionRequired: totals.tuition_required || 0,
                        tuitionPaid: totals.tuition_paid || 0,
                        tuitionBalance: totals.tuition_balance || 0,
                        otherBaseRequired: totals.other_base_required || 0,
                        otherDebt: totals.other_debt || 0,
                        otherRequired: totals.other_required || 0,
                        otherPaid: totals.other_paid || 0,
                        otherBalance: totals.other_balance || 0,
                        totalBaseRequired: totals.total_base_required || 0,
                        totalDebt: totals.total_debt || 0,
                        totalRequired: totals.total_required || 0,
                        totalPaid: totals.total_paid || 0,
                        totalBalance: totals.total_balance || 0,
                        schoolName: 'School Name',
                        schoolReg: '',
                        schoolPhone: '',
                        schoolEmail: '',
                        schoolLogo: null
                    };
                    
                    generatePaymentInvoicePDF(pdfData);
                }
            });
        });
        
        // CSRF Token setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Generate Control Numbers
        $('#generateControlNumbersBtn').on('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will generate control numbers for all active students. Existing control numbers will not be overwritten.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Generate',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const btn = $(this);
                    const originalText = btn.html();
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Generating...');
                    
                    $.ajax({
                        url: '{{ route("generate_control_numbers") }}',
                        method: 'POST',
                        data: {
                            year: $('#filterYear').val()
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                    confirmButtonColor: '#940000'
                                }).then(() => {
                                    loadPaymentsData(); // Reload data via AJAX
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Failed to generate control numbers';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: errorMessage,
                                confirmButtonColor: '#940000'
                            });
                        },
                        complete: function() {
                            btn.prop('disabled', false).html(originalText);
                        }
                    });
                }
            });
        });

        // Send SMS to All Parents - Send control numbers directly
        $('#sendSMSBtn').on('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Send Control Numbers via SMS',
                text: 'This will send control numbers (School Fee and Other Contribution) to all parents who have not received SMS yet.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Send SMS',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const btn = $(this);
                    const originalHtml = btn.html();
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Sending...');
                    
                    $.ajax({
                        url: '/send_control_numbers_sms',
                        method: 'POST',
                        data: {
                            year: $('#filterYear').val()
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    html: `<p>${response.message}</p><p><strong>Sent:</strong> ${response.sent || 0}</p><p><strong>Failed:</strong> ${response.failed || 0}</p>`,
                                    confirmButtonColor: '#940000'
                                }).then(() => {
                                    loadPaymentsData(); // Reload data via AJAX
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message || 'Failed to send SMS',
                                    confirmButtonColor: '#940000'
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Failed to send SMS';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: errorMessage,
                                confirmButtonColor: '#940000'
                            });
                        },
                        complete: function() {
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });

        // Copy Control Number
        $(document).on('click', '.copy-control-btn', function(e) {
            e.preventDefault();
            const controlNumber = $(this).data('control-number');
            
            navigator.clipboard.writeText(controlNumber).then(function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: 'Control number copied to clipboard',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        });

        // View More
        $(document).on('click', '.view-more-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            
            // Store button reference in modal for PDF export
            $('#viewMoreModal').data('view-more-btn', btn);
            
            // Get isClosedYear from button data or from global response
            const isClosedYear = btn.data('is-closed-year') || false;
            
            // Student Photo
            const studentPhoto = btn.data('student-photo') || '';
            const firstLetter = btn.data('student-first-letter') || 'N';
            const placeholderColor = btn.data('student-placeholder-color') || '#940000';
            
            const photoImg = $('#view_student_photo');
            const photoPlaceholder = $('#view_student_photo_placeholder');
            
            // Reset both elements
            photoImg.hide();
            photoPlaceholder.hide();
            
            if (studentPhoto) {
                photoImg.attr('src', studentPhoto);
                photoImg.off('error').on('error', function() {
                    $(this).hide();
                    photoPlaceholder.css({
                        'background-color': placeholderColor,
                        'display': 'flex'
                    }).text(firstLetter);
                });
                photoImg.show();
            } else {
                photoPlaceholder.css({
                    'background-color': placeholderColor,
                    'display': 'flex'
                }).text(firstLetter);
            }
            
            // Student Information
            $('#view_student_name').text(btn.data('student-name') || '-');
            $('#view_student_class').text(btn.data('student-class') || '-');
            $('#view_student_admission').text(btn.data('student-admission') || '-');
            
            // Get payments and totals data
            let payment = {};
            let totals = {};
            try {
                const paymentData = btn.data('payment');
                const totalsData = btn.data('totals');
                
                if (paymentData) {
                    payment = typeof paymentData === 'string' ? JSON.parse(paymentData) : paymentData;
                }
                if (totalsData) {
                    totals = typeof totalsData === 'string' ? JSON.parse(totalsData) : totalsData;
                }
            } catch(e) {
                console.error('Error parsing payments data:', e);
            }
            
            // Extract fee payments from the unified payment record
            const feePayments = payment.fee_payments || [];
            const tuitionPayments = feePayments.filter(fp => fp.is_required);
            const otherFeePayments = feePayments.filter(fp => !fp.is_required);
            
            // Debt handling
            const oldDebt = parseFloat(payment.debt || 0);
            const totalPaid = parseFloat(payment.amount_paid || 0);
            const totalRequired = parseFloat(payment.amount_required || 0);
            const totalOutstanding = parseFloat(payment.balance || 0);
            const currentBillBalance = Math.max(0, totalOutstanding - oldDebt);

            // Populate Summary Dashboard
            $('#view_control_number').text(payment.control_number || 'N/A');
            $('#view_total_paid').text(totalPaid.toLocaleString() + '/=');
            $('#view_bill_balance').text(currentBillBalance.toLocaleString() + '/=');
            $('#view_total_fee_required').text(totalRequired.toLocaleString() + '/=');
            $('#view_old_debt').text(oldDebt.toLocaleString() + '/=');
            $('#view_total_outstanding').text(totalOutstanding.toLocaleString() + '/=');

            // Sponsorship Info
            const sponsorData = btn.data('student-sponsor');
            const sponsorInfoSection = $('#sponsorInfoSection');
            if (sponsorData) {
                let sponsor = typeof sponsorData === 'string' ? JSON.parse(sponsorData) : sponsorData;
                $('#view_sponsor_name').text(sponsor.sponsor_name || '-');
                $('#view_sponsor_contact_person').text(sponsor.contact_person || 'N/A');
                $('#view_sponsor_phone').text(sponsor.phone || '-');
                $('#view_sponsor_email').text(sponsor.email || 'N/A');
                $('#view_sponsor_percentage').text((sponsor.percentage || 0) + '%');
                
                // Use pre-calculated values from totals if available
                const sponsorAmount = totals.sponsor_amount || 0;
                $('#view_sponsor_amount').text(sponsorAmount.toLocaleString() + '/=');
                
                // Optionally show original total if needed
                if (totals.original_total) {
                     $('#view_total_fee_required').html('<span class="text-muted small me-1" style="text-decoration: line-through;">' + totals.original_total.toLocaleString() + '</span> ' + totalRequired.toLocaleString() + '/=');
                }
                
                sponsorInfoSection.show();
            } else {
                sponsorInfoSection.hide();
                // Reset Total Fee Required display if previously modified
                $('#view_total_fee_required').text(totalRequired.toLocaleString() + '/=');
            }
            
            // Build Record Payment button for the whole control number
            let recordPaymentHtml = '';
            if (!isClosedYear) {
                recordPaymentHtml += '<div class="btn-group">';
                recordPaymentHtml += '<button class="btn btn-success record-payment-btn" ' +
                    'data-payment-id="' + payment.paymentID + '" ' +
                    'data-control-number="' + (payment.control_number || '') + '" ' +
                    'data-amount-required="' + (payment.amount_required || 0) + '" ' +
                    'data-amount-paid="' + (payment.amount_paid || 0) + '" ' +
                    'data-balance="' + (payment.balance || 0) + '" ' +
                    'data-fee-type="Unified Payment" ' +
                    'title="Record Payment">' +
                    '<i class="bi bi-cash-coin me-2"></i> Record New Payment' +
                    '</button>';
                
                // Add Send SMS button to modal
                recordPaymentHtml += '<button class="btn btn-outline-primary resend-control-sms-btn" ' +
                    'data-payment-id="' + payment.paymentID + '" ' +
                    'title="Send Control Number SMS">' +
                    '<i class="bi bi-send me-1"></i> Send SMS' +
                    '</button>';
                recordPaymentHtml += '</div>';
            }
            $('#modalRecordPaymentContainer').html(recordPaymentHtml);
            
            // Build Tuition Fees section
            let tuitionHtml = '';
            if (tuitionPayments.length > 0 || oldDebt > 0) {
                tuitionHtml += '<div class="table-responsive">';
                tuitionHtml += '<table class="table table-sm table-bordered table-hover">';
                tuitionHtml += '<thead class="bg-light">';
                tuitionHtml += '<tr>';
                tuitionHtml += '<th>Fee Name</th>';
                tuitionHtml += '<th class="text-end">Required (TZS)</th>';
                tuitionHtml += '<th class="text-end">Paid (TZS)</th>';
                tuitionHtml += '<th class="text-end">Balance (TZS)</th>';
                tuitionHtml += '<th class="text-center">Actions</th>';
                tuitionHtml += '</tr>';
                tuitionHtml += '</thead>';
                tuitionHtml += '<tbody>';
                
                // Add Previous Year Debt if applicable
                if (oldDebt > 0) {
                    tuitionHtml += '<tr class="table-warning">';
                    tuitionHtml += '<td><strong>Previous Year Debt</strong></td>';
                    tuitionHtml += '<td class="text-end">' + oldDebt.toLocaleString() + '/=</td>';
                    tuitionHtml += '<td class="text-end">0.00/=</td>';
                    tuitionHtml += '<td class="text-end"><strong>' + oldDebt.toLocaleString() + '/=</strong></td>';
                    tuitionHtml += '<td class="text-center">-</td>';
                    tuitionHtml += '</tr>';
                }

                tuitionPayments.forEach(function(fp) {
                    tuitionHtml += '<tr>';
                    tuitionHtml += '<td>' + fp.fee_name + '</td>';
                    tuitionHtml += '<td class="text-end">' + parseFloat(fp.fee_total_amount || 0).toLocaleString() + '/=</td>';
                    tuitionHtml += '<td class="text-end text-success">' + parseFloat(fp.amount_paid || 0).toLocaleString() + '/=</td>';
                    tuitionHtml += '<td class="text-end text-danger">' + parseFloat(fp.balance || 0).toLocaleString() + '/=</td>';
                    tuitionHtml += '<td class="text-center">';
                    tuitionHtml += '<button class="btn btn-sm btn-outline-info view-payment-records-btn" ';
                    tuitionHtml += 'data-payment-id="' + payment.paymentID + '" ';
                    tuitionHtml += 'data-control-number="' + (payment.control_number || '') + '" ';
                    tuitionHtml += 'data-fee-name="' + fp.fee_name + '" ';
                    tuitionHtml += 'title="View History"><i class="bi bi-clock-history"></i></button>';
                    tuitionHtml += '</td>';
                    tuitionHtml += '</tr>';
                });
                
                tuitionHtml += '</tbody>';
                tuitionHtml += '</table>';
                tuitionHtml += '</div>';
                
                $('#tuitionFeesContent').html(tuitionHtml);
                $('#tuitionFeesSection').show();
            } else {
                $('#tuitionFeesSection').hide();
            }
            
            // Build Other Fees section
            let otherFeesHtml = '';
            if (otherFeePayments.length > 0) {
                otherFeesHtml += '<div class="table-responsive">';
                otherFeesHtml += '<table class="table table-sm table-bordered table-hover">';
                otherFeesHtml += '<thead class="bg-light">';
                otherFeesHtml += '<tr>';
                otherFeesHtml += '<th>Fee Name</th>';
                otherFeesHtml += '<th class="text-end">Required (TZS)</th>';
                otherFeesHtml += '<th class="text-end">Paid (TZS)</th>';
                otherFeesHtml += '<th class="text-end">Balance (TZS)</th>';
                otherFeesHtml += '<th class="text-center">Actions</th>';
                otherFeesHtml += '</tr>';
                otherFeesHtml += '</thead>';
                otherFeesHtml += '<tbody>';
                
                otherFeePayments.forEach(function(fp) {
                    otherFeesHtml += '<tr>';
                    otherFeesHtml += '<td>' + fp.fee_name + '</td>';
                    otherFeesHtml += '<td class="text-end">' + parseFloat(fp.fee_total_amount || 0).toLocaleString() + '/=</td>';
                    otherFeesHtml += '<td class="text-end text-success">' + parseFloat(fp.amount_paid || 0).toLocaleString() + '/=</td>';
                    otherFeesHtml += '<td class="text-end text-danger">' + parseFloat(fp.balance || 0).toLocaleString() + '/=</td>';
                    otherFeesHtml += '<td class="text-center">';
                    otherFeesHtml += '<button class="btn btn-sm btn-outline-info view-payment-records-btn" ';
                    otherFeesHtml += 'data-payment-id="' + payment.paymentID + '" ';
                    otherFeesHtml += 'data-control-number="' + (payment.control_number || '') + '" ';
                    otherFeesHtml += 'data-fee-name="' + fp.fee_name + '" ';
                    otherFeesHtml += 'title="View History"><i class="bi bi-clock-history"></i></button>';
                    otherFeesHtml += '</td>';
                    otherFeesHtml += '</tr>';
                });
                
                otherFeesHtml += '</tbody>';
                otherFeesHtml += '</table>';
                otherFeesHtml += '</div>';
                    
                $('#otherFeesContent').html(otherFeesHtml);
                $('#otherFeesSection').show();
            } else {
                $('#otherFeesSection').hide();
            }
            
            // Build All Transactions section
            let transactionsHtml = '';
            const paymentRecords = payment.payment_records || [];
            
            // Update collapse button text with control number
            const controlNo = payment.control_number || 'N/A';
            $('#allTransactionsCollapse').parent().find('button span').html('<i class="bi bi-clock-history me-2"></i> All Transactions of Control Number: <span class="text-primary-custom">' + controlNo + '</span>');

            if (paymentRecords.length > 0) {
                transactionsHtml += '<div class="table-responsive">';
                transactionsHtml += '<table class="table table-sm table-bordered table-hover">';
                transactionsHtml += '<thead class="bg-light">';
                transactionsHtml += '<tr>';
                transactionsHtml += '<th>Date</th>';
                transactionsHtml += '<th class="text-end">Amount (TZS)</th>';
                transactionsHtml += '<th>Method</th>';
                transactionsHtml += '<th>Reference</th>';
                transactionsHtml += '<th class="text-center">Status</th>';
                transactionsHtml += '<th class="text-center">Actions</th>';
                transactionsHtml += '</tr>';
                transactionsHtml += '</thead>';
                transactionsHtml += '<tbody>';
                
                paymentRecords.sort((a, b) => new Date(b.payment_date) - new Date(a.payment_date)).forEach(function(record) {
                    const isVerified = record.is_verified || false;
                    
                    // Format Date
                    let displayDate = 'N/A';
                    if (record.payment_date) {
                        const pDate = new Date(record.payment_date);
                        displayDate = pDate.toLocaleString('en-GB', { 
                            day: '2-digit', 
                            month: 'short', 
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    }

                    transactionsHtml += '<tr>';
                    transactionsHtml += '<td>' + displayDate + '</td>';
                    transactionsHtml += '<td class="text-end fw-bold text-success">' + parseFloat(record.paid_amount || 0).toLocaleString() + '/=</td>';
                    transactionsHtml += '<td><span class="badge ' + (record.payment_source === 'Bank' ? 'bg-primary' : 'bg-success') + '">' + (record.payment_source || 'N/A') + '</span></td>';
                    transactionsHtml += '<td>' + (record.reference_number || 'N/A') + '</td>';
                    transactionsHtml += '<td class="text-center">';
                    if (isVerified) {
                        transactionsHtml += '<span class="badge bg-success" title="Verified"><i class="bi bi-patch-check-fill"></i></span>';
                    } else {
                        transactionsHtml += '<span class="badge bg-secondary" title="Unverified"><i class="bi bi-patch-exclamation"></i></span>';
                    }
                    transactionsHtml += '</td>';
                    transactionsHtml += '<td class="text-center">';
                    transactionsHtml += '<div class="btn-group btn-group-sm">';
                    
                    if (!isClosedYear) {
                        // Verify/Unverify Toggle
                        if (isVerified) {
                            transactionsHtml += '<button class="btn btn-outline-secondary unverify-payment-record-btn" ';
                            transactionsHtml += 'data-record-id="' + record.recordID + '" ';
                            transactionsHtml += 'data-payment-id="' + payment.paymentID + '" ';
                            transactionsHtml += 'title="Unverify"><i class="bi bi-patch-minus"></i></button>';
                        } else {
                            transactionsHtml += '<button class="btn btn-outline-success verify-payment-record-btn" ';
                            transactionsHtml += 'data-record-id="' + record.recordID + '" ';
                            transactionsHtml += 'data-payment-id="' + payment.paymentID + '" ';
                            transactionsHtml += 'title="Verify"><i class="bi bi-patch-check"></i></button>';
                        }

                        // Edit button
                        transactionsHtml += '<button class="btn btn-outline-warning edit-payment-record-btn" ';
                        transactionsHtml += 'data-record-id="' + record.recordID + '" ';
                        transactionsHtml += 'data-payment-id="' + payment.paymentID + '" ';
                        transactionsHtml += 'data-paid-amount="' + record.paid_amount + '" ';
                        transactionsHtml += 'data-payment-date="' + (record.payment_date || '') + '" ';
                        transactionsHtml += 'data-payment-source="' + (record.payment_source || '') + '" ';
                        transactionsHtml += 'data-reference-number="' + (record.reference_number || '') + '" ';
                        transactionsHtml += 'data-bank-name="' + (record.bank_name || '') + '" ';
                        transactionsHtml += 'data-notes="' + (record.notes || '') + '" ';
                        transactionsHtml += 'title="Edit"><i class="bi bi-pencil"></i></button>';
                        
                        // Delete button
                        transactionsHtml += '<button class="btn btn-outline-danger delete-payment-record-btn" ';
                        transactionsHtml += 'data-record-id="' + record.recordID + '" ';
                        transactionsHtml += 'data-payment-id="' + payment.paymentID + '" ';
                        transactionsHtml += 'data-paid-amount="' + record.paid_amount + '" ';
                        transactionsHtml += 'title="Delete"><i class="bi bi-trash"></i></button>';
                    } else {
                        transactionsHtml += '-';
                    }
                    
                    transactionsHtml += '</div>';
                    transactionsHtml += '</td>';
                    transactionsHtml += '</tr>';
                });
                
                transactionsHtml += '</tbody>';
                transactionsHtml += '</table>';
                transactionsHtml += '</div>';
            } else {
                transactionsHtml = '<div class="text-center py-3 text-muted">No transactions found for this control number.</div>';
            }
            $('#allTransactionsContent').html(transactionsHtml);
            
            $('#viewMoreModal').modal('show');
        });

        // Modal Export PDF Click Handler
        $(document).on('click', '#modalExportPdfBtn', function() {
            const btn = $('#viewMoreModal').data('view-more-btn');
            if (!btn) return;

            const studentName = btn.data('student-name');
            const studentAdmission = btn.data('student-admission');
            const studentClass = btn.data('student-class');
            const studentPhoto = btn.data('student-photo');
            
            let payment = {};
            try {
                const paymentData = btn.data('payment');
                payment = typeof paymentData === 'string' ? JSON.parse(paymentData) : paymentData;
            } catch(e) {
                console.error('Error parsing payment data for PDF:', e);
            }

            // Prepare data for PDF generation
            const pdfData = {
                schoolName: '{{ $school->school_name ?? "School Name" }}',
                schoolLogo: '{{ $school->logo ? asset("storage/" . $school->logo) : "" }}',
                studentName: studentName,
                studentAdmission: studentAdmission,
                studentClass: studentClass,
                studentPhoto: studentPhoto,
                controlNumber: payment.control_number || 'N/A',
                totalRequired: payment.amount_required || 0,
                totalPaid: payment.amount_paid || 0,
                totalBalance: payment.balance || 0,
                tuitionPaid: payment.amount_paid || 0,
                otherPaid: 0,
                payments: payment.fee_payments || [],
                paymentRecords: payment.payment_records || []
            };

            Swal.fire({
                title: 'Generating PDF...',
                html: 'Please wait while we prepare your invoice',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            generatePaymentInvoicePDF(pdfData);
        });

        // Record Payment Button Click Handler
        $(document).on('click', '.record-payment-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            
            // Get payment data
            const paymentID = btn.data('payment-id');
            const controlNumber = btn.data('control-number');
            const amountRequired = btn.data('amount-required');
            const amountPaid = btn.data('amount-paid');
            const balance = btn.data('balance');
            const feeType = btn.data('fee-type');
            
            // Set modal fields
            $('#record_payment_id').val(paymentID);
            $('#record_control_number').val(controlNumber);
            $('#record_fee_type').val(feeType);
            $('#record_amount_required').val(parseFloat(amountRequired || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=');
            $('#record_amount_paid').val(parseFloat(amountPaid || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=');
            $('#record_balance').val(parseFloat(balance || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=');
            $('#record_paid_amount').val('');
            $('#record_reference_number').val('');
            $('#record_payment_date').val(new Date().toISOString().split('T')[0]);
            $('#record_payment_source').val('Cash');
            $('#record_bank_name').val('');
            $('#record_notes').val('');
            
            // Reset form state
            togglePaymentMethodFields('Cash');
            
            $('#recordPaymentModal').modal('show');
        });

        // Toggle payment method fields
        function togglePaymentMethodFields(paymentMethod) {
            const referenceField = $('#record_reference_number');
            const referenceContainer = $('#record_reference_number_container');
            const referenceRequired = $('#record_reference_required');
            const bankNameContainer = $('#record_bank_name_container');
            const bankNameField = $('#record_bank_name');
            
            if (paymentMethod === 'Bank') {
                // Bank: Reference number required, show bank name
                referenceContainer.show();
                referenceField.prop('required', true);
                referenceRequired.show();
                bankNameContainer.show();
                bankNameField.prop('required', true);
            } else {
                // Cash: Reference number hidden completely, hide bank name
                referenceContainer.hide();
                referenceField.prop('required', false);
                referenceField.val(''); // Clear value
                referenceRequired.hide();
                bankNameContainer.hide();
                bankNameField.prop('required', false);
                bankNameField.val('');
            }
        }
        
        // Payment method change handler
        $(document).on('change', '#record_payment_source', function() {
            const paymentMethod = $(this).val();
            togglePaymentMethodFields(paymentMethod);
        });

        // Record Payment Form Submission
        $(document).on('submit', '#recordPaymentForm', function(e) {
            e.preventDefault();
            
            const formData = $(this).serialize();
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Recording...');
            
            $.ajax({
                url: '/record_payment',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            $('#recordPaymentModal').modal('hide');
                            $('#viewMoreModal').modal('hide');
                            loadPaymentsData(); // Reload payments table
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Failed to record payment'
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to record payment';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).flat().join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: errorMessage
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // View Payment Records Button Click Handler
        $(document).on('click', '.view-payment-records-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            
            // Get payment data
            const paymentID = btn.data('payment-id');
            const controlNumber = btn.data('control-number');
            const feeType = btn.data('fee-type');
            
            // Set modal fields
            $('#view_records_control_number').val(controlNumber);
            $('#view_records_fee_type').val(feeType);
            
            // Show loading
            $('#paymentRecordsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"></div><p class="mt-2">Loading payment records...</p></div>');
            
            // Close viewMoreModal first if it's open, then open payment records modal
            if ($('#viewMoreModal').hasClass('show')) {
                $('#viewMoreModal').modal('hide');
                // Wait for viewMoreModal to close, then open payment records modal
                $('#viewMoreModal').one('hidden.bs.modal', function() {
                    $('#viewPaymentRecordsModal').modal('show');
                });
            } else {
                // If viewMoreModal is not open, open payment records modal immediately
                $('#viewPaymentRecordsModal').modal('show');
            }
            
            // Load payment records
            $.ajax({
                url: '/get_payment_records',
                method: 'GET',
                data: {
                    paymentID: paymentID
                },
                success: function(response) {
                    if (response.success && response.records) {
                        let html = '';
                        
                        if (response.records.length > 0) {
                            html += '<div class="table-responsive">';
                            html += '<table class="table table-bordered table-hover">';
                            html += '<thead style="background-color: #940000; color: white;">';
                            html += '<tr>';
                            html += '<th>#</th>';
                            html += '<th>Payment Date</th>';
                            html += '<th>Paid Amount (TZS)</th>';
                            html += '<th>Payment Method</th>';
                            html += '<th>Reference Number</th>';
                            html += '<th>Status</th>';
                            html += '<th>Notes</th>';
                            html += '<th class="text-center">Actions</th>';
                            html += '</tr>';
                            html += '</thead>';
                            html += '<tbody>';
                            
                            let totalPaid = 0;
                            response.records.forEach(function(record, index) {
                                totalPaid += parseFloat(record.paid_amount || 0);
                                html += '<tr>';
                                html += '<td>' + (index + 1) + '</td>';
                                html += '<td>' + (record.payment_date || 'N/A') + '</td>';
                                html += '<td class="text-end text-success fw-bold">' + parseFloat(record.paid_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</td>';
                                html += '<td><span class="badge ' + (record.payment_source === 'Bank' ? 'bg-primary' : 'bg-success') + '">' + (record.payment_source || 'N/A') + '</span></td>';
                                html += '<td>' + (record.reference_number || 'N/A') + '</td>';
                                html += '<td class="text-center">';
                                if (record.is_verified) {
                                    html += '<span class="badge bg-success" title="Verified"><i class="bi bi-patch-check-fill"></i> Verified</span>';
                                } else {
                                    html += '<span class="badge bg-secondary" title="Pending Verification"><i class="bi bi-patch-exclamation"></i> Unverified</span>';
                                }
                                html += '</td>';
                                html += '<td>' + (record.notes || '-') + '</td>';
                                html += '<td class="text-center">';
                                html += '<div class="btn-group btn-group-sm" role="group">';
                                
                                if (!record.is_verified) {
                                    html += '<button class="btn btn-sm btn-success verify-payment-record-btn" ';
                                    html += 'data-record-id="' + record.recordID + '" ';
                                    html += 'data-payment-id="' + paymentID + '" ';
                                    html += 'title="Verify Payment">';
                                    html += '<i class="bi bi-shield-check"></i>';
                                    html += '</button>';
                                    
                                    html += '<button class="btn btn-sm btn-warning edit-payment-record-btn" ';
                                    html += 'data-record-id="' + record.recordID + '" ';
                                    html += 'data-payment-id="' + paymentID + '" ';
                                    html += 'data-paid-amount="' + record.paid_amount + '" ';
                                    html += 'data-payment-date="' + (record.payment_date || '') + '" ';
                                    html += 'data-payment-source="' + (record.payment_source || '') + '" ';
                                    html += 'data-reference-number="' + (record.reference_number || '') + '" ';
                                    html += 'data-bank-name="' + (record.bank_name || '') + '" ';
                                    html += 'data-notes="' + (record.notes || '') + '" ';
                                    html += 'title="Edit Payment Record">';
                                    html += '<i class="bi bi-pencil"></i>';
                                    html += '</button>';
                                    
                                    html += '<button class="btn btn-sm btn-danger delete-payment-record-btn" ';
                                    html += 'data-record-id="' + record.recordID + '" ';
                                    html += 'data-payment-id="' + paymentID + '" ';
                                    html += 'data-paid-amount="' + record.paid_amount + '" ';
                                    html += 'title="Delete Payment Record">';
                                    html += '<i class="bi bi-trash"></i>';
                                    html += '</button>';
                                } else {
                                    html += '<button class="btn btn-sm btn-info unverify-payment-record-btn" ';
                                    html += 'data-record-id="' + record.recordID + '" ';
                                    html += 'data-payment-id="' + paymentID + '" ';
                                    html += 'title="Cancel Verification">';
                                    html += '<i class="bi bi-shield-x"></i>';
                                    html += '</button>';
                                    
                                    html += '<span class="btn btn-sm btn-light disabled" title="Verified records cannot be edited"><i class="bi bi-lock-fill"></i></span>';
                                }
                                
                                html += '</div>';
                                html += '</td>';
                                html += '</tr>';
                            });
                            
                            html += '</tbody>';
                            html += '<tfoot style="background-color: #f8f9fa;">';
                            html += '<tr>';
                            html += '<th colspan="2" class="text-end">Total Paid:</th>';
                            html += '<th class="text-end text-success fw-bold">' + totalPaid.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</th>';
                            html += '<th colspan="4"></th>';
                            html += '</tr>';
                            html += '</tfoot>';
                            html += '</table>';
                            html += '</div>';
                        } else {
                            html += '<div class="alert alert-info text-center">';
                            html += '<i class="bi bi-info-circle"></i> No payment records found for this payment.';
                            html += '</div>';
                        }
                        
                        $('#paymentRecordsContent').html(html);
                    } else {
                        $('#paymentRecordsContent').html('<div class="alert alert-warning text-center"><i class="bi bi-exclamation-triangle"></i> ' + (response.message || 'Failed to load payment records') + '</div>');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to load payment records';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    $('#paymentRecordsContent').html('<div class="alert alert-danger text-center"><i class="bi bi-x-circle"></i> ' + errorMessage + '</div>');
                }
            });
        });

            // Verify Payment Record
            $(document).on('click', '.verify-payment-record-btn', function() {
                const recordID = $(this).data('record-id');
                const paymentID = $(this).data('payment-id');
                
                if (confirm('Je, unathibitisha kupokea malipo haya?')) {
                    const btn = $(this);
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                    
                    $.ajax({
                        url: '/verify_payment',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            recordID: recordID
                        },
                        success: function(response) {
                            if (response.success) {
                                showNotification(response.message, 'success');
                                // Refresh payment records list
                                $('.view-payment-records-btn[data-payment-id="' + paymentID + '"]').trigger('click');
                                // Refresh main table
                                loadPaymentsData();
                            } else {
                                toastr.error(response.message || 'Kosa limetokea');
                                btn.prop('disabled', false).html('<i class="bi bi-shield-check"></i>');
                            }
                        },
                        error: function() {
                            toastr.error('Kosa la mawasiliano');
                            btn.prop('disabled', false).html('<i class="bi bi-shield-check"></i>');
                        }
                    });
                }
            });
            
            // Unverify Payment Record
            $(document).on('click', '.unverify-payment-record-btn', function() {
                const recordID = $(this).data('record-id');
                const paymentID = $(this).data('payment-id');
                
                if (confirm('Je, una uhakika wa kufuta uthibitisho huu?')) {
                    const btn = $(this);
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                    
                    $.ajax({
                        url: '/unverify_payment',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            recordID: recordID
                        },
                        success: function(response) {
                            if (response.success) {
                                // Refresh payment records list
                                $('.view-payment-records-btn[data-payment-id="' + paymentID + '"]').trigger('click');
                                // Refresh main table
                                loadPaymentsData();
                            } else {
                                toastr.error(response.message || 'Kosa limetokea');
                                btn.prop('disabled', false).html('<i class="bi bi-shield-x"></i>');
                            }
                        },
                        error: function() {
                            toastr.error('Kosa la mawasiliano');
                            btn.prop('disabled', false).html('<i class="bi bi-shield-x"></i>');
                        }
                    });
                }
            });
            
            // Handle verification UI (ensure toastr is available for these calls)
            function showNotification(message, type) {
                if (typeof toastr !== 'undefined') {
                    toastr[type](message);
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: type === 'success' ? 'success' : 'error',
                        title: type === 'success' ? 'Success' : 'Error',
                        text: message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    alert(message);
                }
            }

        // Edit Payment Record Button Click Handler
        $(document).on('click', '.edit-payment-record-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            
            // Get record data
            const recordID = btn.data('record-id');
            const paymentID = btn.data('payment-id');
            const paidAmount = btn.data('paid-amount');
            const paymentDate = btn.data('payment-date');
            const paymentSource = btn.data('payment-source');
            const referenceNumber = btn.data('reference-number');
            const bankName = btn.data('bank-name');
            const notes = btn.data('notes');
            
            // Set modal fields
            $('#edit_record_id').val(recordID);
            $('#edit_payment_id').val(paymentID);
            $('#edit_paid_amount').val(paidAmount);
            $('#edit_payment_date').val(paymentDate);
            $('#edit_payment_source').val(paymentSource);
            $('#edit_reference_number').val(referenceNumber || '');
            $('#edit_bank_name').val(bankName || '');
            $('#edit_notes').val(notes || '');
            
            // Show/hide reference number and bank name based on payment source
            if (paymentSource === 'Bank') {
                $('#edit_reference_number_group').show();
                $('#edit_bank_name_group').show();
                $('#edit_reference_number').prop('required', true);
                $('#edit_bank_name').prop('required', true);
            } else {
                $('#edit_reference_number_group').hide();
                $('#edit_bank_name_group').hide();
                $('#edit_reference_number').prop('required', false);
                $('#edit_bank_name').prop('required', false);
            }
            
            // Open modal
            $('#editPaymentRecordModal').modal('show');
        });
        
        // Payment Source Change Handler for Edit Modal
        $('#edit_payment_source').on('change', function() {
            const paymentSource = $(this).val();
            if (paymentSource === 'Bank') {
                $('#edit_reference_number_group').show();
                $('#edit_bank_name_group').show();
                $('#edit_reference_number').prop('required', true);
                $('#edit_bank_name').prop('required', true);
            } else {
                $('#edit_reference_number_group').hide();
                $('#edit_bank_name_group').hide();
                $('#edit_reference_number').prop('required', false);
                $('#edit_bank_name').prop('required', false);
                $('#edit_reference_number').val('');
                $('#edit_bank_name').val('');
            }
        });
        
        // Edit Payment Record Form Submission
        $(document).on('submit', '#editPaymentRecordForm', function(e) {
            e.preventDefault();
            
            const formData = $(this).serialize();
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');
            
            $.ajax({
                url: '/update_payment_record',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            $('#editPaymentRecordModal').modal('hide');
                            // Reload payment records
                            const paymentID = $('#edit_payment_id').val();
                            $('.view-payment-records-btn[data-payment-id="' + paymentID + '"]').first().click();
                            loadPaymentsData(); // Reload payments table
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Failed to update payment record'
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to update payment record';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).flat().join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: errorMessage
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
        
        // Delete Payment Record Button Click Handler
        $(document).on('click', '.delete-payment-record-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            const recordID = btn.data('record-id');
            const paymentID = btn.data('payment-id');
            const paidAmount = btn.data('paid-amount');
            
            Swal.fire({
                title: 'Delete Payment Record?',
                html: `Are you sure you want to delete this payment record?<br><strong>Amount: TZS ${parseFloat(paidAmount || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0})}/=</strong><br><br>This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const originalHtml = btn.html();
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
                    
                    $.ajax({
                        url: '/delete_payment_record',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            recordID: recordID,
                            paymentID: paymentID
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                    confirmButtonColor: '#940000'
                                }).then(() => {
                                    // Reload payment records
                                    $('.view-payment-records-btn[data-payment-id="' + paymentID + '"]').first().click();
                                    loadPaymentsData(); // Reload payments table
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message || 'Failed to delete payment record',
                                    confirmButtonColor: '#940000'
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Failed to delete payment record';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: errorMessage,
                                confirmButtonColor: '#940000'
                            });
                        },
                        complete: function() {
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });

        // Send Control Number Button Click Handler
        $(document).on('click', '.send-control-number-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            const paymentID = btn.data('payment-id');
            const controlNumber = btn.data('control-number');
            const feeType = btn.data('fee-type');
            
            Swal.fire({
                title: 'Send Control Number?',
                text: `Are you sure you want to send control number ${controlNumber} to parent?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Send',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const originalHtml = btn.html();
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
                    
                    $.ajax({
                        url: `/resend_control_number/${paymentID}`,
                        method: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                    confirmButtonColor: '#940000'
                                }).then(() => {
                                    loadPaymentsData(); // Reload data via AJAX
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message || 'Failed to send control number',
                                    confirmButtonColor: '#940000'
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Failed to send control number';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: errorMessage,
                                confirmButtonColor: '#940000'
                            });
                        },
                        complete: function() {
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });

        // Resend SMS
        $(document).on('click', '.resend-sms-btn', function(e) {
            e.preventDefault();
            const paymentID = $(this).data('payment-id');
            const studentName = $(this).data('student-name');
            
            Swal.fire({
                title: 'Are you sure?',
                text: `Resend control number SMS to parent of ${studentName}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Resend',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const btn = $(this);
                    const originalHtml = btn.html();
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
                    
                    $.ajax({
                        url: `/resend_control_number/${paymentID}`,
                        method: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                    confirmButtonColor: '#940000'
                                }).then(() => {
                                    loadPaymentsData(); // Reload data via AJAX
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Failed to send SMS';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: errorMessage,
                                confirmButtonColor: '#940000'
                            });
                        },
                        complete: function() {
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });

        // Update Payment
        $(document).on('click', '.update-payment-btn', function(e) {
            e.preventDefault();
            const paymentID = $(this).data('payment-id');
            const amountRequired = $(this).data('amount-required');
            const amountPaid = $(this).data('amount-paid');
            
            $('#update_payment_id').val(paymentID);
            $('#update_amount_required').val('TZS ' + parseFloat(amountRequired).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#update_amount_paid_current').val('TZS ' + parseFloat(amountPaid).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#update_amount_paid').val('');
            $('#update_payment_reference').val('');
            $('#update_notes').val('');
            
            $('#updatePaymentModal').modal('show');
        });

        // Update Payment Form Submission
        $('#updatePaymentForm').on('submit', function(e) {
            e.preventDefault();
            
            const paymentID = $('#update_payment_id').val();
            const formData = $(this).serialize();
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
            
            $.ajax({
                url: `/update_payment_status/${paymentID}`,
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            $('#updatePaymentModal').modal('hide');
                            loadPaymentsData(); // Reload data via AJAX
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to update payment';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage,
                        confirmButtonColor: '#940000'
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Resend Individual Control SMS from Modal
        $(document).on('click', '.resend-control-sms-btn', function() {
            const btn = $(this);
            const paymentID = btn.data('payment-id');
            const originalHtml = btn.html();

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Sending...');

            $.ajax({
                url: '/resend_control_number/' + paymentID,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    Swal.fire({
                        icon: response.success ? 'success' : 'error',
                        title: response.success ? 'Sent!' : 'Failed!',
                        text: response.message,
                        confirmButtonColor: '#940000'
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to send SMS.',
                        confirmButtonColor: '#940000'
                    });
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });



        // Load initial data on startup
        console.log('Starting Payments Management App...');
        loadPaymentsData();
    }
    
    // App is started via waitForJQuery at the top
})();
</script>

<!-- ===== FEE SMS PROGRESS MODAL ===== -->
<div class="modal fade" id="feeSmsProgressModal" tabindex="-1" role="dialog" aria-labelledby="feeSmsProgressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="feeSmsProgressModalLabel">
                    <i class="bi bi-send-check"></i> <span id="feeSmsModalTitle">Send SMS</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info border-0 shadow-sm mb-3" id="feeSmsInfoAlert">
                    <i class="bi bi-info-circle-fill"></i> <span id="feeSmsModalDesc">Chagua wazazi/sponsors wa kupokea SMS.</span>
                </div>

                <!-- Loading state -->
                <div id="feeSmsLoadingState" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Inapakua orodha ya wapokeaji...</p>
                </div>

                <!-- Recipients table (hidden until loaded) -->
                <div id="feeSmsRecipientsSection" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="feeSelectAllParents">
                            <label class="custom-control-label font-weight-bold" for="feeSelectAllParents">Select All</label>
                        </div>
                        <span class="badge badge-primary" id="feeSelectedCount" style="font-size: 0.95rem;">Wapokeaji: 0</span>
                    </div>

                    <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                        <table class="table table-sm table-hover" id="feeSmsTable">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th style="width:40px;"></th>
                                    <th>Mpokeaji</th>
                                    <th>Simu</th>
                                    <th class="text-center" style="width:110px;">Hali</th>
                                </tr>
                            </thead>
                            <tbody id="feeSmsRecipientsList">
                                <!-- Populated via JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Progress section (hidden until sending) -->
                    <div id="feeSmsProgressArea" class="mt-3 d-none">
                        <hr>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="font-weight-bold text-primary-custom">Maendeleo ya Kutuma</span>
                            <span id="feeSmsProgressText" class="small">0 / 0</span>
                        </div>
                        <div class="progress" style="height: 24px; border-radius: 12px; border: 1px solid #ddd;">
                            <div id="feeSmsProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                 role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                        <div id="feeSmsDeliverySummary" class="text-center small mt-2"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="feeBtnCancelSms">Ghairi</button>
                <button type="button" class="btn btn-primary-custom" id="feeStartSendingSms" disabled>
                    <i class="bi bi-send"></i> Anza Kutuma SMS
                </button>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')
