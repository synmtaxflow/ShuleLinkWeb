@include('includes.parent_nav')

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
    .control-number {
        font-family: 'Courier New', monospace;
        font-weight: 700;
        color: #940000;
    }
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                    <h4 class="mb-0">
                        <i class="bi bi-credit-card"></i> Payments & Fees
                    </h4>
                    <p class="mb-0 mt-2">View payments and fees for all your children</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4" id="statisticsCards">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Required</h6>
                            <h3 class="mb-0 text-primary-custom" id="statTotalRequired">TZS 0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Paid</h6>
                            <h3 class="mb-0 text-success" id="statTotalPaid">TZS 0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Outstanding Balance</h6>
                            <h3 class="mb-0 text-danger" id="statOutstandingBalance">TZS 0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Pending Payments</h6>
                            <h3 class="mb-0 text-warning" id="statPendingPayments">0</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fee Type Summary -->
            <div class="row mb-4" id="feeTypeSummary">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-book text-success"></i> Tuition Fees Total
                            </h6>
                            <h3 class="mb-0 text-success" id="statTuitionFees">TZS 0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-wallet2 text-warning"></i> Other Fees Total
                            </h6>
                            <h3 class="mb-0 text-warning" id="statOtherFees">TZS 0.00</h3>
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
                                <i class="bi bi-person"></i> Student
                            </label>
                            <select class="form-select" id="filterStudent">
                                <option value="">All Students</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->studentID }}">
                                        {{ $student->first_name }} {{ $student->last_name }}
                                    </option>
                                @endforeach
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
                                <i class="bi bi-arrow-clockwise"></i> Actions
                            </label>
                            <button type="button" class="btn btn-outline-secondary w-100" id="clearFiltersBtn" title="Clear Filters">
                                <i class="bi bi-x-circle"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary-custom text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Payments & Fees List
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
                        <table class="table table-hover table-sm mb-0" id="paymentsTable" style="width: 100%;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th style="width: 60px;">Photo</th>
                                    <th style="min-width: 150px;">Student Name</th>
                                    <th style="min-width: 100px;">Fee Type</th>
                                    <th style="min-width: 120px;">Control Number</th>
                                    <th style="min-width: 120px;">Amount Required</th>
                                    <th style="min-width: 120px;">Amount Paid</th>
                                    <th style="min-width: 120px;">Balance</th>
                                    <th style="min-width: 100px;">Status</th>
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

@include('includes.footer')

<script>
(function() {
    function initParentPayments() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initParentPayments, 100);
            return;
        }
        
        var $ = jQuery;
        
        // CSRF Token setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Function to generate placeholder color
        function getPlaceholderColor(name) {
            if (!name) return '#940000';
            var colors = ['#940000', '#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6f42c1', '#e83e8c', '#fd7e14', '#20c997'];
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
            var studentFilter = $('#filterStudent').val();
            var feeType = $('#filterFeeType').val();

            // Show loading
            $('#paymentsTableBody').html('<tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-primary-custom" role="status"></div><p class="mt-2">Loading payments...</p></td></tr>');

            $.ajax({
                url: '{{ route("get_parent_payments_ajax") }}',
                type: 'GET',
                data: {
                    search: search,
                    year: year,
                    student: studentFilter,
                    fee_type: feeType
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        var html = '';
                        if (response.data.length > 0) {
                            response.data.forEach(function(item) {
                                var student = item.student;
                                var payment = item.payment;
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
                                        'onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">' +
                                        '<div class="student-photo-placeholder d-none" style="background-color: ' + placeholderColor + ';">' + firstLetter + '</div>' +
                                        '</div>';
                                } else {
                                    photoHtml = '<div class="photo-container">' +
                                        '<div class="student-photo-placeholder" style="background-color: ' + placeholderColor + ';">' + firstLetter + '</div>' +
                                        '</div>';
                                }

                                if (payment) {
                                    // Payment row
                                    var feeTypeBadge = payment.fee_type === 'Tuition Fees' ? 
                                        '<span class="badge bg-success">Tuition Fees</span>' : 
                                        '<span class="badge bg-warning text-dark">Other Fees</span>';
                                    
                                    var statusBadge = '';
                                    if (payment.payment_status === 'Pending') {
                                        statusBadge = '<span class="badge bg-warning">Pending</span>';
                                    } else if (payment.payment_status === 'Incomplete Payment' || payment.payment_status === 'Partial') {
                                        statusBadge = '<span class="badge bg-info">Incomplete</span>';
                                    } else if (payment.payment_status === 'Paid') {
                                        statusBadge = '<span class="badge bg-success">Paid</span>';
                                    } else {
                                        statusBadge = '<span class="badge bg-secondary">Overpaid</span>';
                                    }

                                    var studentFullName = (student.first_name || '') + ' ' + (student.middle_name || '') + ' ' + (student.last_name || '');
                                    var studentClass = (student.subclass && student.subclass.subclass_name) ? student.subclass.subclass_name : 'N/A';

                                    html += '<tr>' +
                                        '<td>' + index + '</td>' +
                                        '<td>' + photoHtml + '</td>' +
                                        '<td><strong>' + studentFullName.trim() + '</strong><br><small class="text-muted">' + (student.admission_number || 'N/A') + ' - ' + studentClass + '</small></td>' +
                                        '<td>' + feeTypeBadge + '</td>' +
                                        '<td><span class="control-number" style="font-size: 0.85rem;">' + (payment.control_number || 'N/A') + '</span></td>' +
                                        '<td><strong class="text-primary-custom">TZS ' + parseFloat(payment.amount_required || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</strong></td>' +
                                        '<td><strong class="text-success">TZS ' + parseFloat(payment.amount_paid || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</strong></td>' +
                                        '<td><strong class="text-danger">TZS ' + parseFloat(payment.balance || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</strong></td>' +
                                        '<td>' + statusBadge + '</td>' +
                                        '</tr>';
                                }
                            });
                        } else {
                            html = '<tr><td colspan="9" class="text-center py-4 text-muted"><i class="bi bi-inbox" style="font-size: 2rem;"></i><p class="mt-2">No payments found</p></td></tr>';
                        }
                        $('#paymentsTableBody').html(html);
                        
                        // Update statistics
                        if (response.statistics) {
                            var stats = response.statistics;
                            $('#statTotalRequired').text('TZS ' + parseFloat(stats.total_required || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#statTotalPaid').text('TZS ' + parseFloat(stats.total_paid || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#statOutstandingBalance').text('TZS ' + parseFloat(stats.total_balance || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#statPendingPayments').text(stats.pending_payments || 0);
                            $('#statTuitionFees').text('TZS ' + parseFloat(stats.tuition_fees_total || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#statOtherFees').text('TZS ' + parseFloat(stats.other_fees_total || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        }
                    } else {
                        $('#paymentsTableBody').html('<tr><td colspan="9" class="text-center py-4 text-danger">Error loading payments</td></tr>');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading payments:', xhr);
                    $('#paymentsTableBody').html('<tr><td colspan="9" class="text-center py-4 text-danger">Error loading payments. Please try again.</td></tr>');
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

        $('#filterYear, #filterStudent, #filterFeeType').on('change', function() {
            loadPaymentsData();
        });

        // Clear all filters
        $('#clearFiltersBtn').on('click', function() {
            $('#searchStudentInput').val('');
            $('#filterYear').val('{{ isset($currentYear) ? $currentYear : date("Y") }}');
            $('#filterStudent').val('');
            $('#filterFeeType').val('');
            loadPaymentsData();
        });

        // Load initial data
        loadPaymentsData();
    }

    // Initialize when DOM and jQuery are ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initParentPayments, 200);
        });
    } else {
        setTimeout(initParentPayments, 200);
    }
})();
</script>

