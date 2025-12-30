@if($user_type == 'Admin')
@include('includes.Admin_nav')
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
        width: 5%;
    }
    #paymentsTable th:nth-child(2),
    #paymentsTable td:nth-child(2) {
        width: 6%;
    }
    #paymentsTable th:nth-child(3),
    #paymentsTable td:nth-child(3) {
        width: 20%;
        white-space: normal;
    }
    #paymentsTable th:nth-child(4),
    #paymentsTable td:nth-child(4) {
        width: 15%;
        text-align: right;
    }
    #paymentsTable th:nth-child(5),
    #paymentsTable td:nth-child(5) {
        width: 15%;
        text-align: right;
    }
    #paymentsTable th:nth-child(6),
    #paymentsTable td:nth-child(6) {
        width: 15%;
        text-align: right;
    }
    #paymentsTable th:nth-child(7),
    #paymentsTable td:nth-child(7) {
        width: 12%;
    }
    #paymentsTable th:nth-child(8),
    #paymentsTable td:nth-child(8) {
        width: 12%;
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
                                <i class="bi bi-send"></i> Send SMS to Parents
                            </button>
                            <button class="btn btn-light text-primary-custom fw-bold" type="button" id="exportInvoiceBtn">
                                <i class="bi bi-file-earmark-pdf"></i> Export PDF Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4" id="statisticsCards">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Pending Payments</h6>
                            <h3 class="mb-0 text-warning" id="statPendingPayments">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Incomplete Payments</h6>
                            <h3 class="mb-0 text-info" id="statIncompletePayments">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Fully Paid</h6>
                            <h3 class="mb-0 text-success" id="statPaidPayments">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Balance</h6>
                            <h3 class="mb-0 text-danger" id="statTotalBalance">TZS 0.00</h3>
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
                        <div class="col-md-3">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-search"></i> Search Student
                            </label>
                            <input type="text" class="form-control" id="searchStudentInput" placeholder="Search by student name or admission number...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-calendar"></i> Year
                            </label>
                            <select class="form-select" id="filterYear">
                                @php
                                    $currentYear = $currentYear ?? date('Y');
                                    $availableYears = $availableYears ?? [];
                                @endphp
                                @if(count($availableYears) > 0)
                                    @foreach($availableYears as $availableYear)
                                        <option value="{{ $availableYear }}" {{ $availableYear == $currentYear ? 'selected' : '' }}>
                                            {{ $availableYear }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="{{ $currentYear }}" selected>{{ $currentYear }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-filter"></i> Fee Type
                            </label>
                            <select class="form-select" id="filterFeeType">
                                <option value="">All Types</option>
                                <option value="Tuition Fees">Tuition Fees</option>
                                <option value="Other Fees">Other Fees</option>
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
                                <i class="bi bi-send"></i> SMS Status
                            </label>
                            <select class="form-select" id="filterSmsStatus">
                                <option value="">All</option>
                                <option value="Yes">Sent</option>
                                <option value="No">Not Sent</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-arrow-clockwise"></i> Actions
                            </label>
                            <button type="button" class="btn btn-outline-secondary w-100" id="clearFiltersBtn" title="Clear Filters">
                                <i class="bi bi-x-circle"></i>
                            </button>
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
                                    <th>#</th>
                                    <th>Photo</th>
                                    <th>Student Name</th>
                                    <th>Total Fee Required (TZS)</th>
                                    <th>Paid Amount (TZS)</th>
                                    <th>Bill Balance (TZS)</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
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
                <div class="row mb-4">
                    <div class="col-md-4 text-center mb-3">
                        <div class="student-photo-modal-container" style="display: inline-block; position: relative;">
                            <img id="view_student_photo" src="" alt="Student Photo" 
                                class="img-thumbnail" 
                                style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px; display: none;"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div id="view_student_photo_placeholder" 
                                class="student-photo-placeholder d-none" 
                                style="width: 120px; height: 120px; border-radius: 8px; display: none; align-items: center; justify-content: center; font-size: 48px; font-weight: bold; color: white; margin: 0 auto;">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 mb-3">
                        <h5 class="text-primary-custom mb-2" id="view_student_name">-</h5>
                        <p class="mb-1"><strong>Class:</strong> <span id="view_student_class">-</span></p>
                    </div>
                </div>

                <!-- Tuition Fees Details -->
                <div class="row mb-3" id="tuitionFeesSection" style="display: none;">
                    <div class="col-12">
                        <div id="tuitionFeesContent">
                            <!-- Tuition fees will be dynamically inserted here -->
                        </div>
                    </div>
                </div>

                <!-- Other Fees Details -->
                <div class="row mb-3" id="otherFeesSection" style="display: none;">
                    <div class="col-12">
                        <div id="otherFeesContent">
                            <!-- Other fees will be dynamically inserted here -->
                        </div>
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
                        <label class="form-label fw-bold">Paid Amount (TZS) <span class="text-danger">*</span></label>
                        <input type="number" name="paid_amount" id="record_paid_amount" class="form-control" placeholder="0.00" step="0.01" min="0.01" required>
                        <small class="text-muted">Enter the amount being paid</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Reference Number <span class="text-danger">*</span></label>
                        <input type="text" name="reference_number" id="record_reference_number" class="form-control" placeholder="e.g., BANK123456" required>
                        <small class="text-muted">Unique reference number (from bank or cash payment)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" id="record_payment_date" class="form-control" required>
                        <small class="text-muted">Date when payment was made</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_source" id="record_payment_source" class="form-select" required>
                            <option value="Cash">Cash</option>
                            <option value="Bank">Bank</option>
                        </select>
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

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- jsPDF Library for PDF generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script>
    // Ensure jsPDF is available globally
    if (typeof window.jspdf !== 'undefined' && !window.jsPDF) {
        window.jsPDF = window.jspdf.jsPDF;
    }
</script>

<script>
(function() {
    function initPaymentsManagement() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initPaymentsManagement, 100);
            return;
        }
        
        var $ = jQuery;
        
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

        // Function to load payments data via AJAX
        function loadPaymentsData() {
            var search = $('#searchStudentInput').val();
            var year = $('#filterYear').val();
            var feeType = $('#filterFeeType').val();
            var paymentStatus = $('#filterPaymentStatus').val();
            var smsStatus = $('#filterSmsStatus').val();

            // Show loading
            $('#paymentsTableBody').html('<tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"></div><p class="mt-2">Loading payments...</p></td></tr>');

            $.ajax({
                url: '{{ route("get_payments_ajax") }}',
                type: 'GET',
                data: {
                    search: search,
                    year: year,
                    fee_type: feeType,
                    payment_status: paymentStatus,
                    sms_status: smsStatus
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        var html = '';
                        if (response.data.length > 0) {
                            response.data.forEach(function(item) {
                                var student = item.student;
                                var payments = item.payments || [];
                                var totals = item.totals || {};
                                var index = item.index;

                                // Generate placeholder
                                var firstName = student.first_name || '';
                                var firstLetter = firstName ? firstName.charAt(0).toUpperCase() : 'N';
                                var fullName = (student.first_name || '') + ' ' + (student.last_name || '');
                                var placeholderColor = getPlaceholderColor(fullName);

                                // Photo HTML
                                var photoHtml = '';
                                if (student.photo) {
                                    photoHtml = '<div class="photo-container">' +
                                        '<img src="' + student.photo + '" alt="Student Photo" class="student-photo" ' +
                                        'data-first-letter="' + firstLetter + '" ' +
                                        'data-placeholder-color="' + placeholderColor + '" ' +
                                        'onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">' +
                                        '<div class="student-photo-placeholder d-none" style="background-color: ' + placeholderColor + ';">' + firstLetter + '</div>' +
                                        '</div>';
                                } else {
                                    photoHtml = '<div class="photo-container">' +
                                        '<div class="student-photo-placeholder" style="background-color: ' + placeholderColor + ';">' + firstLetter + '</div>' +
                                        '</div>';
                                }

                                var studentFullName = (student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '');
                                var parentName = (student.parent && student.parent.first_name) ? 
                                    student.parent.first_name + ' ' + (student.parent.last_name || '') : 'N/A';
                                var parentPhone = (student.parent && student.parent.phone) ? student.parent.phone : 'N/A';
                                var studentClass = (student.subclass && student.subclass.subclass_name) ? student.subclass.subclass_name : 'N/A';

                                // Total Fee Required
                                var totalFeeRequiredHtml = '<div style="font-size: 0.9rem;">';
                                if (totals.total_required > 0) {
                                    totalFeeRequiredHtml += '<strong class="text-primary-custom">' + 
                                        parseFloat(totals.total_required || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</strong>';
                                } else {
                                    totalFeeRequiredHtml += '<span class="text-muted">0/=</span>';
                                }
                                totalFeeRequiredHtml += '</div>';

                                // Paid Amount
                                var paidAmountHtml = '<div style="font-size: 0.9rem;">';
                                if (totals.total_paid > 0) {
                                    paidAmountHtml += '<strong class="text-success">' + 
                                        parseFloat(totals.total_paid || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</strong>';
                                } else {
                                    paidAmountHtml += '<span class="text-muted">0/=</span>';
                                }
                                paidAmountHtml += '</div>';

                                // Bill Balance
                                var billBalanceHtml = '<div style="font-size: 0.9rem;">';
                                    if (totals.total_balance > 0) {
                                    billBalanceHtml += '<strong class="text-danger">' + 
                                        parseFloat(totals.total_balance || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</strong>';
                                } else {
                                    billBalanceHtml += '<span class="text-success">0/=</span>';
                                }
                                billBalanceHtml += '</div>';

                                // Status badge
                                var overallStatus = totals.overall_status || 'Pending';
                                var statusBadge = '';
                                if (overallStatus === 'Pending') {
                                    statusBadge = '<span class="badge bg-warning">Pending</span>';
                                } else if (overallStatus === 'Incomplete Payment' || overallStatus === 'Partial') {
                                    statusBadge = '<span class="badge bg-info">Incomplete</span>';
                                } else if (overallStatus === 'Paid') {
                                    statusBadge = '<span class="badge bg-success">Paid</span>';
                                } else {
                                    statusBadge = '<span class="badge bg-secondary">Overpaid</span>';
                                }

                                // Store payments data as JSON for modal
                                var paymentsJson = JSON.stringify(payments);
                                console.log('Payments JSON for student:', student.studentID, paymentsJson);
                                console.log('Payments array for student:', student.studentID, payments);

                                html += '<tr>' +
                                    '<td>' + index + '</td>' +
                                    '<td>' + photoHtml + '</td>' +
                                    '<td><strong>' + studentFullName.trim() + '</strong><br><small class="text-muted">' + (student.admission_number || 'N/A') + '</small></td>' +
                                    '<td class="text-end">' + totalFeeRequiredHtml + '</td>' +
                                    '<td class="text-end">' + paidAmountHtml + '</td>' +
                                    '<td class="text-end">' + billBalanceHtml + '</td>' +
                                    '<td>' + statusBadge + '</td>' +
                                    '<td>' +
                                    '<div class="btn-group btn-group-sm" role="group">' +
                                    '<button class="btn btn-outline-primary btn-sm view-more-btn" ' +
                                    'data-student-id="' + student.studentID + '" ' +
                                    'data-student-name="' + studentFullName.trim() + '" ' +
                                    'data-student-admission="' + (student.admission_number || 'N/A') + '" ' +
                                    'data-student-class="' + studentClass + '" ' +
                                    'data-student-photo="' + (student.photo || '') + '" ' +
                                    'data-student-first-letter="' + firstLetter + '" ' +
                                    'data-student-placeholder-color="' + placeholderColor + '" ' +
                                    'data-parent-name="' + parentName + '" ' +
                                    'data-parent-phone="' + parentPhone + '" ' +
                                    'data-payments=\'' + paymentsJson.replace(/'/g, "&#39;") + '\' ' +
                                    'data-totals=\'' + JSON.stringify(totals).replace(/'/g, "&#39;") + '\' ' +
                                    'title="View More Details"><i class="bi bi-eye"></i></button>' +
                                    '</div></td></tr>';
                            });
                        } else {
                            html = '<tr><td colspan="8" class="text-center py-4 text-muted"><i class="bi bi-inbox" style="font-size: 2rem;"></i><p class="mt-2">No payments found</p></td></tr>';
                        }
                        $('#paymentsTableBody').html(html);
                        
                        // Update statistics
                        if (response.statistics) {
                            var stats = response.statistics;
                            $('#statPendingPayments').text(stats.pending_payments || 0);
                            $('#statIncompletePayments').text(stats.incomplete_payments || 0);
                            $('#statPaidPayments').text(stats.paid_payments || 0);
                            $('#statTotalBalance').text('TZS ' + parseFloat(stats.total_balance || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#statTotalRequired').text('TZS ' + parseFloat(stats.total_required || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#statTotalPaid').text('TZS ' + parseFloat(stats.total_paid || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#statOutstandingBalance').text('TZS ' + parseFloat(stats.total_balance || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        }
                        
                        // Reinitialize DataTable
                        if ($.fn.DataTable.isDataTable('#paymentsTable')) {
                            $('#paymentsTable').DataTable().destroy();
                        }
                        $('#paymentsTable').DataTable({
                            dom: 'Bfrtip',
                            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                            pageLength: 25,
                            order: [[0, 'asc']],
                            scrollX: false,
                            autoWidth: false,
                            fixedColumns: false,
                            responsive: false,
                            columnDefs: [
                                { width: "5%", targets: 0 },
                                { width: "6%", targets: 1 },
                                { width: "20%", targets: 2 },
                                { width: "15%", targets: 3 },
                                { width: "15%", targets: 4 },
                                { width: "15%", targets: 5 },
                                { width: "12%", targets: 6 },
                                { width: "12%", targets: 7 }
                            ],
                            language: {
                                search: "Search:",
                                lengthMenu: "Show _MENU_ entries",
                                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                                infoEmpty: "No entries to show",
                                infoFiltered: "(filtered from _MAX_ total entries)"
                            }
                        });
                    } else {
                        $('#paymentsTableBody').html('<tr><td colspan="8" class="text-center py-4 text-danger">Error loading payments</td></tr>');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading payments:', xhr);
                    $('#paymentsTableBody').html('<tr><td colspan="8" class="text-center py-4 text-danger">Error loading payments. Please try again.</td></tr>');
                }
            });
        }

        // Real-time filtering with debounce
        var searchTimeout;
        $('#searchStudentInput').on('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                loadPaymentsData();
            }, 500);
        });

        $('#filterYear, #filterFeeType, #filterPaymentStatus, #filterSmsStatus').on('change', function() {
            loadPaymentsData();
        });

        // Clear all filters
        $('#clearFiltersBtn').on('click', function() {
            $('#searchStudentInput').val('');
            $('#filterYear').val('{{ isset($currentYear) ? $currentYear : date("Y") }}');
            $('#filterFeeType').val('');
            $('#filterPaymentStatus').val('');
            $('#filterSmsStatus').val('');
            loadPaymentsData();
        });

        // Load initial data
        loadPaymentsData();

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
                        var tuitionPayments = data.payments.filter(function(p) { return p.fee_type === 'Tuition Fees'; });
                            var otherPayments = data.payments.filter(function(p) { return p.fee_type === 'Other Fees'; });
                            
                            // Calculate totals from payments array - always calculate to ensure accuracy
                            var tuitionRequired = tuitionPayments.reduce(function(sum, p) { 
                                return sum + parseFloat(p.amount_required || p.amountRequired || 0); 
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
                            
                            var otherRequired = otherPayments.reduce(function(sum, p) { 
                                return sum + parseFloat(p.amount_required || p.amountRequired || 0); 
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
                            if (data.tuitionRequired !== undefined && data.tuitionRequired !== null && parseFloat(data.tuitionRequired) > 0) {
                                tuitionRequired = parseFloat(data.tuitionRequired);
                            }
                            if (data.otherRequired !== undefined && data.otherRequired !== null && parseFloat(data.otherRequired) > 0) {
                                otherRequired = parseFloat(data.otherRequired);
                            }
                            
                            // Recalculate balances
                            tuitionBalance = tuitionRequired - tuitionPaid;
                            otherBalance = otherRequired - otherPaid;
                            
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
                                head: [['Required Amount (TZS)', 'Paid Amount (TZS)', 'Bill Balance (TZS)', 'Status']],
                                body: [[
                                    parseFloat(overallRequired).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                    parseFloat(overallPaid).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                    parseFloat(overallBalance).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                    overallStatus
                                ]],
                                startY: yPos,
                                styles: { 
                                    fontSize: 9,
                                    cellPadding: 3,
                                    lineColor: [0, 0, 0],
                                    lineWidth: 0.1
                                },
                                headStyles: {
                                    fillColor: [148, 0, 0],
                                    textColor: 255,
                                    fontStyle: 'bold',
                                    fontSize: 9,
                                    halign: 'center'
                                },
                                columnStyles: {
                                    0: { halign: 'right', cellWidth: 45, fontStyle: 'bold' },
                                    1: { halign: 'right', cellWidth: 45, textColor: [40, 167, 69], fontStyle: 'bold' },
                                    2: { halign: 'right', cellWidth: 45, textColor: [220, 53, 69], fontStyle: 'bold' },
                                    3: { cellWidth: 50, halign: 'center', fontStyle: 'bold' }
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
                                    
                                    var amountRequired = parseFloat(payment.amount_required || payment.amountRequired || 0);
                                    var balance = parseFloat(payment.balance || 0);
                                    
                                    // Recalculate balance if needed
                                    if (balance === 0 && amountRequired > 0) {
                                        balance = amountRequired - amountPaid;
                                    }
                                    
                                    console.log('Tuition Payment - amountRequired:', amountRequired, 'amountPaid:', amountPaid, 'balance:', balance);
                                    
                                    tuitionTableData.push([
                                        payment.control_number || 'N/A',
                                        amountRequired.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                        amountPaid.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                        balance.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/='
                                    ]);
                                });
                                
                                // Title as caption (centered above table)
                                doc.setTextColor(148, 0, 0);
                                doc.setFontSize(12);
                                doc.setFont('helvetica', 'bold');
                                doc.text('Tuition Fees', pageWidth / 2, yPos, { align: 'center' });
                    yPos += 7;
                    
                                doc.autoTable({
                                    head: [['Control Number', 'Required Amount (TZS)', 'Paid Amount (TZS)', 'Bill Balance (TZS)']],
                                    body: tuitionTableData,
                                    startY: yPos,
                                    styles: { 
                                        fontSize: 9,
                                        cellPadding: 3,
                                        lineColor: [0, 0, 0],
                                        lineWidth: 0.1
                                    },
                                    headStyles: {
                                        fillColor: [148, 0, 0],
                                        textColor: 255,
                                        fontStyle: 'bold',
                                        fontSize: 9,
                                        halign: 'center'
                                    },
                                    columnStyles: {
                                        0: { cellWidth: 50, halign: 'center' },
                                        1: { halign: 'right', cellWidth: 45 },
                                        2: { halign: 'right', cellWidth: 45, textColor: [40, 167, 69] },
                                        3: { halign: 'right', cellWidth: 45, textColor: [220, 53, 69] }
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
                                    
                                    var amountRequired = parseFloat(payment.amount_required || payment.amountRequired || 0);
                                    var balance = parseFloat(payment.balance || 0);
                                    
                                    // Recalculate balance if needed
                                    if (balance === 0 && amountRequired > 0) {
                                        balance = amountRequired - amountPaid;
                                    }
                                    
                                    console.log('Other Payment - amountRequired:', amountRequired, 'amountPaid:', amountPaid, 'balance:', balance);
                                    
                                    otherTableData.push([
                                        payment.control_number || 'N/A',
                                        amountRequired.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                        amountPaid.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=',
                                        balance.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/='
                                    ]);
                                });
                                
                                // Title as caption (centered above table)
                            doc.setTextColor(148, 0, 0);
                                doc.setFontSize(12);
                                doc.setFont('helvetica', 'bold');
                                doc.text('Other Fees', pageWidth / 2, yPos, { align: 'center' });
                                yPos += 7;
                                
                                doc.autoTable({
                                    head: [['Control Number', 'Required Amount (TZS)', 'Paid Amount (TZS)', 'Bill Balance (TZS)']],
                                    body: otherTableData,
                                    startY: yPos,
                                    styles: { 
                                        fontSize: 9,
                                        cellPadding: 3,
                                        lineColor: [0, 0, 0],
                                        lineWidth: 0.1
                                    },
                                    headStyles: {
                                        fillColor: [148, 0, 0],
                                        textColor: 255,
                                        fontStyle: 'bold',
                                        fontSize: 9,
                                        halign: 'center'
                                    },
                                    columnStyles: {
                                        0: { cellWidth: 50, halign: 'center' },
                                        1: { halign: 'right', cellWidth: 45 },
                                        2: { halign: 'right', cellWidth: 45, textColor: [40, 167, 69] },
                                        3: { halign: 'right', cellWidth: 45, textColor: [220, 53, 69] }
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
                        doc.text('Powered by EmCa Technology', pageWidth / 2, pageHeight - 5, { align: 'center' });
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
                        doc.text('Powered by EmCa Technology', pageWidth / 2, pageHeight - 5, { align: 'center' });
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
                    
                    // Extract from tuition fees table
                    tuitionTable.each(function() {
                        const row = $(this);
                        const controlNumber = row.find('.control-number').text() || row.find('td').eq(0).text();
                        const requiredText = row.find('td').eq(1).text().replace(/[^\d.]/g, '');
                        const paidText = row.find('td').eq(2).text().replace(/[^\d.]/g, '');
                        const balanceText = row.find('td').eq(3).text().replace(/[^\d.]/g, '');
                        
                        if (controlNumber && controlNumber !== 'N/A') {
                            payments.push({
                                fee_type: 'Tuition Fees',
                                control_number: controlNumber,
                                amount_required: parseFloat(requiredText) || 0,
                                amount_paid: parseFloat(paidText) || 0,
                                balance: parseFloat(balanceText) || 0
                            });
                        }
                    });
                    
                    // Extract from other fees table
                    otherFeesTable.each(function() {
                        const row = $(this);
                        const controlNumber = row.find('.control-number').text() || row.find('td').eq(0).text();
                        const requiredText = row.find('td').eq(1).text().replace(/[^\d.]/g, '');
                        const paidText = row.find('td').eq(2).text().replace(/[^\d.]/g, '');
                        const balanceText = row.find('td').eq(3).text().replace(/[^\d.]/g, '');
                        
                        if (controlNumber && controlNumber !== 'N/A') {
                            payments.push({
                                fee_type: 'Other Fees',
                                control_number: controlNumber,
                                amount_required: parseFloat(requiredText) || 0,
                                amount_paid: parseFloat(paidText) || 0,
                                balance: parseFloat(balanceText) || 0
                            });
                        }
                    });
                    
                    // Recalculate totals from extracted payments
                    const tuitionPayments = payments.filter(p => p.fee_type === 'Tuition Fees');
                    const otherFeePayments = payments.filter(p => p.fee_type === 'Other Fees');
                    
                    totals = {
                        tuition_required: tuitionPayments.reduce((sum, p) => sum + (p.amount_required || 0), 0),
                        tuition_paid: tuitionPayments.reduce((sum, p) => sum + (p.amount_paid || 0), 0),
                        tuition_balance: tuitionPayments.reduce((sum, p) => sum + (p.balance || 0), 0),
                        other_required: otherFeePayments.reduce((sum, p) => sum + (p.amount_required || 0), 0),
                        other_paid: otherFeePayments.reduce((sum, p) => sum + (p.amount_paid || 0), 0),
                        other_balance: otherFeePayments.reduce((sum, p) => sum + (p.balance || 0), 0),
                        total_required: (tuitionPayments.reduce((sum, p) => sum + (p.amount_required || 0), 0) + 
                                        otherFeePayments.reduce((sum, p) => sum + (p.amount_required || 0), 0)),
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
                        tuitionRequired: totals.tuition_required || 0,
                        tuitionPaid: totals.tuition_paid || 0,
                        tuitionBalance: totals.tuition_balance || 0,
                        otherRequired: totals.other_required || 0,
                        otherPaid: totals.other_paid || 0,
                        otherBalance: totals.other_balance || 0,
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
                        tuitionRequired: totals.tuition_required || 0,
                        tuitionPaid: totals.tuition_paid || 0,
                        tuitionBalance: totals.tuition_balance || 0,
                        otherRequired: totals.other_required || 0,
                        otherPaid: totals.other_paid || 0,
                        otherBalance: totals.other_balance || 0,
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
                text: 'This will send control numbers (Tuition Fees and Other Fees) to all parents who have not received SMS yet.',
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
            
            // Get payments and totals data
            let payments = [];
            let totals = {};
            try {
                const paymentsData = btn.data('payments');
                const totalsData = btn.data('totals');
                
                if (paymentsData) {
                    payments = typeof paymentsData === 'string' ? JSON.parse(paymentsData) : paymentsData;
                }
                if (totalsData) {
                    totals = typeof totalsData === 'string' ? JSON.parse(totalsData) : totalsData;
                }
            } catch(e) {
                console.error('Error parsing payments data:', e);
            }
            
            // Calculate totals for summary
            const tuitionPayments = payments.filter(p => p.fee_type === 'Tuition Fees');
            const otherFeePayments = payments.filter(p => p.fee_type === 'Other Fees');
            
            const totalRequired = (tuitionPayments.reduce((sum, p) => sum + parseFloat(p.amount_required || 0), 0) + 
                                  otherFeePayments.reduce((sum, p) => sum + parseFloat(p.amount_required || 0), 0));
            const totalPaid = (tuitionPayments.reduce((sum, p) => sum + parseFloat(p.amount_paid || 0), 0) + 
                              otherFeePayments.reduce((sum, p) => sum + parseFloat(p.amount_paid || 0), 0));
            const totalBalance = (tuitionPayments.reduce((sum, p) => sum + parseFloat(p.balance || 0), 0) + 
                                 otherFeePayments.reduce((sum, p) => sum + parseFloat(p.balance || 0), 0));
            
            // Determine overall status
            let overallStatus = 'Pending';
            if (totalBalance <= 0 && totalPaid > 0) {
                overallStatus = totalPaid > totalRequired ? 'Overpaid' : 'Paid';
            } else if (totalPaid > 0 && totalBalance > 0) {
                overallStatus = 'Incomplete Payment';
            } else if (totalPaid > 0 && totalPaid < totalRequired) {
                overallStatus = 'Partial';
            }
            
            // Build Summary Table - Simple totals only
            let summaryHtml = '';
            if (payments.length > 0) {
                summaryHtml += '<div class="table-responsive mb-4">';
                summaryHtml += '<table class="table table-bordered">';
                summaryHtml += '<thead style="background-color: #940000; color: white;">';
                summaryHtml += '<tr>';
                summaryHtml += '<th class="text-end">Required Amount (TZS)</th>';
                summaryHtml += '<th class="text-end">Paid Amount (TZS)</th>';
                summaryHtml += '<th class="text-end">Bill Balance (TZS)</th>';
                summaryHtml += '<th class="text-center">Status</th>';
                summaryHtml += '</tr>';
                summaryHtml += '</thead>';
                summaryHtml += '<tbody>';
                summaryHtml += '<tr>';
                summaryHtml += '<td class="text-end"><strong>' + parseFloat(totalRequired).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</strong></td>';
                summaryHtml += '<td class="text-end text-success"><strong>' + parseFloat(totalPaid).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</strong></td>';
                summaryHtml += '<td class="text-end text-danger"><strong>' + parseFloat(totalBalance).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</strong></td>';
                summaryHtml += '<td class="text-center">';
                let statusBadge = '';
                if (overallStatus === 'Paid') {
                    statusBadge = '<span class="badge bg-success">Paid</span>';
                } else if (overallStatus === 'Overpaid') {
                    statusBadge = '<span class="badge bg-info">Overpaid</span>';
                } else if (overallStatus === 'Incomplete Payment' || overallStatus === 'Partial') {
                    statusBadge = '<span class="badge bg-warning">' + overallStatus + '</span>';
                } else {
                    statusBadge = '<span class="badge bg-secondary">Pending</span>';
                }
                summaryHtml += statusBadge;
                summaryHtml += '</td>';
                summaryHtml += '</tr>';
                summaryHtml += '</tbody>';
                summaryHtml += '</table>';
                summaryHtml += '</div>';
            }
            
            // Build Tuition Fees section - Simple table
            let tuitionHtml = '';
            if (tuitionPayments.length > 0) {
                        tuitionHtml += '<div class="table-responsive">';
                tuitionHtml += '<table class="table table-bordered table-hover" style="margin-bottom: 20px;">';
                tuitionHtml += '<caption style="caption-side: top; font-size: 1.2rem; font-weight: bold; color: #940000; padding: 12px 0; text-align: center; margin-bottom: 10px;">';
                tuitionHtml += '<i class="bi bi-book"></i> Tuition Fees';
                tuitionHtml += '</caption>';
                tuitionHtml += '<thead style="background-color: #940000; color: white;">';
                tuitionHtml += '<tr>';
                tuitionHtml += '<th>Control Number</th>';
                tuitionHtml += '<th class="text-end">Required Amount (TZS)</th>';
                tuitionHtml += '<th class="text-end">Paid Amount (TZS)</th>';
                tuitionHtml += '<th class="text-end">Bill Balance (TZS)</th>';
                tuitionHtml += '<th class="text-center">Actions</th>';
                tuitionHtml += '</tr>';
                tuitionHtml += '</thead>';
                        tuitionHtml += '<tbody>';
                        
                tuitionPayments.forEach(function(payment) {
                            tuitionHtml += '<tr>';
                    tuitionHtml += '<td><span class="control-number">' + (payment.control_number || 'N/A') + '</span></td>';
                    tuitionHtml += '<td class="text-end">' + parseFloat(payment.amount_required || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</td>';
                    tuitionHtml += '<td class="text-end text-success">' + parseFloat(payment.amount_paid || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</td>';
                    tuitionHtml += '<td class="text-end text-danger">' + parseFloat(payment.balance || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</td>';
                    tuitionHtml += '<td class="text-center">';
                    tuitionHtml += '<div class="btn-group btn-group-sm" role="group">';
                    tuitionHtml += '<button class="btn btn-sm btn-success record-payment-btn" ';
                    tuitionHtml += 'data-payment-id="' + payment.paymentID + '" ';
                    tuitionHtml += 'data-control-number="' + (payment.control_number || '') + '" ';
                    tuitionHtml += 'data-amount-required="' + (payment.amount_required || 0) + '" ';
                    tuitionHtml += 'data-amount-paid="' + (payment.amount_paid || 0) + '" ';
                    tuitionHtml += 'data-balance="' + (payment.balance || 0) + '" ';
                    tuitionHtml += 'data-fee-type="Tuition Fees" ';
                    tuitionHtml += 'title="Record Payment">';
                    tuitionHtml += '<i class="bi bi-cash-coin"></i>';
                    tuitionHtml += '</button>';
                    tuitionHtml += '<button class="btn btn-sm btn-info view-payment-records-btn" ';
                    tuitionHtml += 'data-payment-id="' + payment.paymentID + '" ';
                    tuitionHtml += 'data-control-number="' + (payment.control_number || '') + '" ';
                    tuitionHtml += 'data-fee-type="Tuition Fees" ';
                    tuitionHtml += 'title="View Payment Records">';
                    tuitionHtml += '<i class="bi bi-eye"></i>';
                    tuitionHtml += '</button>';
                    tuitionHtml += '<button class="btn btn-sm btn-warning send-control-number-btn" ';
                    tuitionHtml += 'data-payment-id="' + payment.paymentID + '" ';
                    tuitionHtml += 'data-control-number="' + (payment.control_number || '') + '" ';
                    tuitionHtml += 'data-fee-type="Tuition Fees" ';
                    tuitionHtml += 'title="Send Control Number to Parent">';
                    tuitionHtml += '<i class="bi bi-send"></i>';
                    tuitionHtml += '</button>';
                    tuitionHtml += '</div>';
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
            
            // Display Summary Table
            // Remove any existing summary table first
            $('.payment-summary-table').remove();
            if (summaryHtml) {
                // Insert summary before tuition fees section
                if ($('#tuitionFeesSection').is(':visible')) {
                    $('#tuitionFeesSection').before('<div class="payment-summary-table">' + summaryHtml + '</div>');
                } else if ($('#otherFeesSection').is(':visible')) {
                    $('#otherFeesSection').before('<div class="payment-summary-table">' + summaryHtml + '</div>');
                } else {
                    // If no sections visible, add after student info
                    $('#viewMoreModal .modal-body .row:first').after('<div class="payment-summary-table">' + summaryHtml + '</div>');
                }
            }
            
            // Build Other Fees section - Simple table
            let otherFeesHtml = '';
            if (otherFeePayments.length > 0) {
                        otherFeesHtml += '<div class="table-responsive">';
                otherFeesHtml += '<table class="table table-bordered table-hover" style="margin-bottom: 20px;">';
                otherFeesHtml += '<caption style="caption-side: top; font-size: 1.2rem; font-weight: bold; color: #940000; padding: 12px 0; text-align: center; margin-bottom: 10px;">';
                otherFeesHtml += '<i class="bi bi-list-check"></i> Other Fees';
                otherFeesHtml += '</caption>';
                otherFeesHtml += '<thead style="background-color: #940000; color: white;">';
                otherFeesHtml += '<tr>';
                otherFeesHtml += '<th>Control Number</th>';
                otherFeesHtml += '<th class="text-end">Required Amount (TZS)</th>';
                otherFeesHtml += '<th class="text-end">Paid Amount (TZS)</th>';
                otherFeesHtml += '<th class="text-end">Bill Balance (TZS)</th>';
                otherFeesHtml += '<th class="text-center">Actions</th>';
                otherFeesHtml += '</tr>';
                otherFeesHtml += '</thead>';
                        otherFeesHtml += '<tbody>';
                        
                otherFeePayments.forEach(function(payment) {
                            otherFeesHtml += '<tr>';
                    otherFeesHtml += '<td><span class="control-number">' + (payment.control_number || 'N/A') + '</span></td>';
                    otherFeesHtml += '<td class="text-end">' + parseFloat(payment.amount_required || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</td>';
                    otherFeesHtml += '<td class="text-end text-success">' + parseFloat(payment.amount_paid || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</td>';
                    otherFeesHtml += '<td class="text-end text-danger">' + parseFloat(payment.balance || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</td>';
                    otherFeesHtml += '<td class="text-center">';
                    otherFeesHtml += '<div class="btn-group btn-group-sm" role="group">';
                    otherFeesHtml += '<button class="btn btn-sm btn-success record-payment-btn" ';
                    otherFeesHtml += 'data-payment-id="' + payment.paymentID + '" ';
                    otherFeesHtml += 'data-control-number="' + (payment.control_number || '') + '" ';
                    otherFeesHtml += 'data-amount-required="' + (payment.amount_required || 0) + '" ';
                    otherFeesHtml += 'data-amount-paid="' + (payment.amount_paid || 0) + '" ';
                    otherFeesHtml += 'data-balance="' + (payment.balance || 0) + '" ';
                    otherFeesHtml += 'data-fee-type="Other Fees" ';
                    otherFeesHtml += 'title="Record Payment">';
                    otherFeesHtml += '<i class="bi bi-cash-coin"></i>';
                    otherFeesHtml += '</button>';
                    otherFeesHtml += '<button class="btn btn-sm btn-info view-payment-records-btn" ';
                    otherFeesHtml += 'data-payment-id="' + payment.paymentID + '" ';
                    otherFeesHtml += 'data-control-number="' + (payment.control_number || '') + '" ';
                    otherFeesHtml += 'data-fee-type="Other Fees" ';
                    otherFeesHtml += 'title="View Payment Records">';
                    otherFeesHtml += '<i class="bi bi-eye"></i>';
                    otherFeesHtml += '</button>';
                    otherFeesHtml += '<button class="btn btn-sm btn-warning send-control-number-btn" ';
                    otherFeesHtml += 'data-payment-id="' + payment.paymentID + '" ';
                    otherFeesHtml += 'data-control-number="' + (payment.control_number || '') + '" ';
                    otherFeesHtml += 'data-fee-type="Other Fees" ';
                    otherFeesHtml += 'title="Send Control Number to Parent">';
                    otherFeesHtml += '<i class="bi bi-send"></i>';
                    otherFeesHtml += '</button>';
                    otherFeesHtml += '</div>';
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
            
            $('#viewMoreModal').modal('show');
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
            $('#record_notes').val('');
            
            $('#recordPaymentModal').modal('show');
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
            
            // Open modal
            $('#viewPaymentRecordsModal').modal('show');
            
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
                            html += '<th>Notes</th>';
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
                                html += '<td>' + (record.notes || '-') + '</td>';
                                html += '</tr>';
                            });
                            
                            html += '</tbody>';
                            html += '<tfoot style="background-color: #f8f9fa;">';
                            html += '<tr>';
                            html += '<th colspan="2" class="text-end">Total Paid:</th>';
                            html += '<th class="text-end text-success fw-bold">' + totalPaid.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</th>';
                            html += '<th colspan="3"></th>';
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
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initPaymentsManagement, 200);
        });
    } else {
        setTimeout(initPaymentsManagement, 200);
    }
})();
</script>

@include('includes.footer')
