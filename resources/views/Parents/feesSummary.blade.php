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
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="bi bi-list-check"></i> Fees Summary
                            </h4>
                            <p class="mb-0 mt-2">View fees summary for all your children by class</p>
                        </div>
                        <button class="btn btn-light text-primary-custom fw-bold" type="button" id="requestControlNumberBtn">
                            <i class="bi bi-key"></i> Request Control Numbers
                        </button>
                    </div>
                </div>
            </div>

            <!-- Year Filter -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-calendar"></i> Select Year
                            </label>
                            <select class="form-select" id="summaryYear">
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
                        <div class="col-md-8 text-end">
                            <button type="button" class="btn btn-outline-primary" id="refreshSummaryBtn">
                                <i class="bi bi-arrow-clockwise"></i> Refresh Summary
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fees Summary Content -->
            <div id="feesSummaryContent">
                <!-- Fees summary will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- View More Details Modal -->
<div class="modal fade" id="viewFeeDetailsModal" tabindex="-1" aria-labelledby="viewFeeDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewFeeDetailsModalLabel">
                    <i class="bi bi-eye"></i> Fee Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewFeeDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<script>
(function() {
    function initFeesSummary() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initFeesSummary, 100);
            return;
        }
        
        var $ = jQuery;
        
        // CSRF Token setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Load Fees Summary
        function loadFeesSummary() {
            var year = $('#summaryYear').val();

            $('#feesSummaryContent').html('<div class="text-center py-5"><div class="spinner-border text-primary-custom" role="status" style="width: 3rem; height: 3rem;"></div><p class="mt-3">Loading fees summary...</p></div>');

            $.ajax({
                url: '{{ route("get_fees_summary_ajax") }}',
                type: 'GET',
                data: {
                    year: year
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        var html = '';
                        if (response.data.length > 0) {
                            response.data.forEach(function(item) {
                                var tuitionStatusBadge = '';
                                if (item.tuition_fees.status === 'Paid') {
                                    tuitionStatusBadge = '<span class="badge bg-success">Paid</span>';
                                } else if (item.tuition_fees.status === 'Pending' || item.tuition_fees.status === 'Incomplete Payment' || item.tuition_fees.status === 'Partial') {
                                    tuitionStatusBadge = '<span class="badge bg-warning">Pending</span>';
                                } else {
                                    tuitionStatusBadge = '<span class="badge bg-secondary">No Payment</span>';
                                }

                                var otherStatusBadge = '';
                                if (item.other_fees.status === 'Paid') {
                                    otherStatusBadge = '<span class="badge bg-success">Paid</span>';
                                } else if (item.other_fees.status === 'Pending' || item.other_fees.status === 'Incomplete Payment' || item.other_fees.status === 'Partial') {
                                    otherStatusBadge = '<span class="badge bg-warning">Pending</span>';
                                } else {
                                    otherStatusBadge = '<span class="badge bg-secondary">No Payment</span>';
                                }

                                html += '<div class="card mb-4 border-0 shadow-sm" style="border-left: 4px solid #940000;">';
                                html += '<div class="card-body">';
                                html += '<div class="row mb-3">';
                                html += '<div class="col-md-12">';
                                html += '<h5 class="mb-1"><strong><i class="bi bi-person"></i> ' + item.student_name + '</strong></h5>';
                                html += '<p class="mb-0 text-muted"><i class="bi bi-card-text"></i> Admission: <strong>' + item.admission_number + '</strong> | <i class="bi bi-mortarboard"></i> Class: <strong>' + item.class + '</strong></p>';
                                html += '</div>';
                                html += '</div>';
                                
                                html += '<div class="row">';
                                
                                // Tuition Fees
                                html += '<div class="col-md-6 mb-3">';
                                html += '<div class="card bg-light h-100">';
                                html += '<div class="card-body">';
                                html += '<h6 class="text-success mb-3"><i class="bi bi-book"></i> Tuition Fees</h6>';
                                html += '<div class="row mb-2">';
                                html += '<div class="col-6"><small class="text-muted d-block mb-1">Required:</small><strong class="text-primary-custom">TZS ' + parseFloat(item.tuition_fees.required || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</strong></div>';
                                html += '<div class="col-6"><small class="text-muted d-block mb-1">Paid:</small><strong class="text-success">TZS ' + parseFloat(item.tuition_fees.paid || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</strong></div>';
                                html += '</div>';
                                html += '<div class="row mb-2">';
                                html += '<div class="col-6"><small class="text-muted d-block mb-1">Balance:</small><strong class="text-danger">TZS ' + parseFloat(item.tuition_fees.balance || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</strong></div>';
                                html += '<div class="col-6"><small class="text-muted d-block mb-1">Status:</small>' + tuitionStatusBadge + '</div>';
                                html += '</div>';
                                if (item.tuition_fees.control_number) {
                                    html += '<div class="mt-3 pt-3 border-top">';
                                    html += '<small class="text-muted d-block mb-1">Control Number:</small>';
                                    html += '<span class="control-number" style="font-size: 1.1rem;">' + item.tuition_fees.control_number + '</span>';
                                    html += '</div>';
                                } else {
                                    html += '<div class="mt-3 pt-3 border-top">';
                                    html += '<small class="text-muted">No control number assigned yet</small>';
                                    html += '</div>';
                                }
                                // View More Button for Tuition Fees
                                html += '<div class="mt-3 pt-3 border-top text-center">';
                                html += '<button class="btn btn-sm btn-outline-primary view-more-fee-btn" ' +
                                    'data-student-id="' + item.studentID + '" ' +
                                    'data-student-name="' + (item.student_name || '').replace(/"/g, '&quot;') + '" ' +
                                    'data-fee-type="Tuition Fees" ' +
                                    'data-installments="' + encodeURIComponent(JSON.stringify(item.tuition_fees.installments || [])) + '" ' +
                                    'data-allow-partial="' + (item.tuition_fees.allow_partial_payment ? '1' : '0') + '">';
                                html += '<i class="bi bi-eye"></i> View More Details';
                                html += '</button>';
                                html += '</div>';
                                html += '</div>';
                                html += '</div>';
                                html += '</div>';

                                // Other Fees
                                html += '<div class="col-md-6 mb-3">';
                                html += '<div class="card bg-light h-100">';
                                html += '<div class="card-body">';
                                html += '<h6 class="text-warning mb-3"><i class="bi bi-wallet2"></i> Other Fees</h6>';
                                html += '<div class="row mb-2">';
                                html += '<div class="col-6"><small class="text-muted d-block mb-1">Required:</small><strong class="text-primary-custom">TZS ' + parseFloat(item.other_fees.required || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</strong></div>';
                                html += '<div class="col-6"><small class="text-muted d-block mb-1">Paid:</small><strong class="text-success">TZS ' + parseFloat(item.other_fees.paid || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</strong></div>';
                                html += '</div>';
                                html += '<div class="row mb-2">';
                                html += '<div class="col-6"><small class="text-muted d-block mb-1">Balance:</small><strong class="text-danger">TZS ' + parseFloat(item.other_fees.balance || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</strong></div>';
                                html += '<div class="col-6"><small class="text-muted d-block mb-1">Status:</small>' + otherStatusBadge + '</div>';
                                html += '</div>';
                                if (item.other_fees.control_number) {
                                    html += '<div class="mt-3 pt-3 border-top">';
                                    html += '<small class="text-muted d-block mb-1">Control Number:</small>';
                                    html += '<span class="control-number" style="font-size: 1.1rem;">' + item.other_fees.control_number + '</span>';
                                    html += '</div>';
                                } else {
                                    html += '<div class="mt-3 pt-3 border-top">';
                                    html += '<small class="text-muted">No control number assigned yet</small>';
                                    html += '</div>';
                                }
                                // View More Button for Other Fees
                                html += '<div class="mt-3 pt-3 border-top text-center">';
                                html += '<button class="btn btn-sm btn-outline-warning view-more-fee-btn" ' +
                                    'data-student-id="' + item.studentID + '" ' +
                                    'data-student-name="' + (item.student_name || '').replace(/"/g, '&quot;') + '" ' +
                                    'data-fee-type="Other Fees" ' +
                                    'data-installments="' + encodeURIComponent(JSON.stringify(item.other_fees.installments || [])) + '" ' +
                                    'data-allow-partial="' + (item.other_fees.allow_partial_payment ? '1' : '0') + '" ' +
                                    'data-other-fees-details="' + encodeURIComponent(JSON.stringify(item.other_fees.other_fees_details || [])) + '">';
                                html += '<i class="bi bi-eye"></i> View More Details';
                                html += '</button>';
                                html += '</div>';
                                html += '</div>';
                                html += '</div>';
                                html += '</div>';

                                html += '</div>';

                                // Total Summary
                                html += '<div class="col-md-12 mt-3">';
                                html += '<div class="card bg-primary-custom text-white">';
                                html += '<div class="card-body">';
                                html += '<h6 class="mb-3"><i class="bi bi-calculator"></i> Total Summary</h6>';
                                html += '<div class="row text-center">';
                                html += '<div class="col-md-4">';
                                html += '<small class="d-block mb-2">Total Required</small>';
                                html += '<h4 class="mb-0">TZS ' + parseFloat(item.total.required || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</h4>';
                                html += '</div>';
                                html += '<div class="col-md-4">';
                                html += '<small class="d-block mb-2">Total Paid</small>';
                                html += '<h4 class="mb-0">TZS ' + parseFloat(item.total.paid || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</h4>';
                                html += '</div>';
                                html += '<div class="col-md-4">';
                                html += '<small class="d-block mb-2">Total Balance</small>';
                                html += '<h4 class="mb-0">TZS ' + parseFloat(item.total.balance || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</h4>';
                                html += '</div>';
                                html += '</div>';
                                html += '</div>';
                                html += '</div>';
                                html += '</div>';

                                html += '</div>';
                                html += '</div>';
                            });
                        } else {
                            html = '<div class="card border-0 shadow-sm">';
                            html += '<div class="card-body text-center py-5">';
                            html += '<i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>';
                            html += '<p class="mt-3 mb-0 text-muted">No fees summary found for the selected year</p>';
                            html += '</div>';
                            html += '</div>';
                        }
                        $('#feesSummaryContent').html(html);
                    } else {
                        $('#feesSummaryContent').html('<div class="card border-0 shadow-sm"><div class="card-body text-center py-5 text-danger">Error loading fees summary</div></div>');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading fees summary:', xhr);
                    $('#feesSummaryContent').html('<div class="card border-0 shadow-sm"><div class="card-body text-center py-5 text-danger">Error loading fees summary. Please try again.</div></div>');
                }
            });
        }

        // Request Control Number Button
        $('#requestControlNumberBtn').on('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Request Control Numbers',
                text: 'This will generate control numbers for all your children who don\'t have them yet. Continue?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Request',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const btn = $(this);
                    const originalText = btn.html();
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Requesting...');
                    
                    // Get all active student IDs only
                    var studentIDs = [];
                    @foreach($students as $student)
                        @if($student->status === 'Active')
                            studentIDs.push({{ $student->studentID }});
                        @endif
                    @endforeach

                    if (studentIDs.length === 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'No students found',
                            confirmButtonColor: '#940000'
                        });
                        btn.prop('disabled', false).html(originalText);
                        return;
                    }

                    // Request control numbers for each student
                    var requests = [];
                    var successCount = 0;
                    var failCount = 0;
                    var messages = [];

                    studentIDs.forEach(function(studentID) {
                        requests.push(
                            $.ajax({
                                url: '/request_control_number/' + studentID,
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType: 'json',
                                success: function(response) {
                                    if (response.success) {
                                        successCount++;
                                        if (response.details && response.details.length > 0) {
                                            messages = messages.concat(response.details);
                                        }
                                    } else {
                                        failCount++;
                                        if (response.message) {
                                            messages.push('Error for student ' + studentID + ': ' + response.message);
                                        }
                                    }
                                },
                                error: function(xhr, status, error) {
                                    failCount++;
                                    var errorMsg = 'Error for student ' + studentID;
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMsg += ': ' + xhr.responseJSON.message;
                                    } else {
                                        errorMsg += ': ' + error;
                                    }
                                    messages.push(errorMsg);
                                    console.error('Error requesting control number for student ' + studentID + ':', xhr);
                                }
                            })
                        );
                    });

                    Promise.allSettled(requests).then(function(results) {
                        var message = '';
                        var hasSuccess = successCount > 0;
                        var hasFailures = failCount > 0;
                        
                        if (hasSuccess && !hasFailures) {
                            message = 'Control numbers requested successfully!';
                            if (messages.length > 0) {
                                message += '\n\n' + messages.join('\n');
                            }
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: message,
                                confirmButtonColor: '#940000'
                            }).then(() => {
                                loadFeesSummary();
                            });
                        } else if (hasSuccess && hasFailures) {
                            message = 'Some control numbers were generated successfully, but some failed.\n\n';
                            message += 'Success: ' + successCount + '\n';
                            message += 'Failed: ' + failCount + '\n\n';
                            if (messages.length > 0) {
                                message += messages.join('\n');
                            }
                            
                            Swal.fire({
                                icon: 'warning',
                                title: 'Partial Success',
                                text: message,
                                confirmButtonColor: '#940000'
                            }).then(() => {
                                loadFeesSummary();
                            });
                        } else {
                            message = 'Failed to generate control numbers.\n\n';
                            if (messages.length > 0) {
                                message += messages.join('\n');
                            } else {
                                message += 'Please try again or contact support.';
                            }
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: message,
                                confirmButtonColor: '#940000'
                            });
                        }
                    }).finally(function() {
                        btn.prop('disabled', false).html(originalText);
                    });
                }
            });
        });

        // Load fees summary when year changes
        $('#summaryYear').on('change', function() {
            loadFeesSummary();
        });

        // Refresh button
        $('#refreshSummaryBtn').on('click', function() {
            loadFeesSummary();
        });

        // View More Fee Details Button
        $(document).on('click', '.view-more-fee-btn', function() {
            var btn = $(this);
            var studentName = btn.data('student-name');
            var feeType = btn.data('fee-type');
            
            // Parse JSON from data attributes (decode URI component first)
            var installmentsData = btn.attr('data-installments');
            var installments = [];
            try {
                if (installmentsData) {
                    installments = JSON.parse(decodeURIComponent(installmentsData));
                }
            } catch(e) {
                console.error('Error parsing installments:', e);
                installments = [];
            }
            
            var allowPartial = btn.data('allow-partial') == '1';
            
            // Parse other fees details for Other Fees
            var otherFeesDetailsData = btn.attr('data-other-fees-details');
            var otherFeesDetails = [];
            try {
                if (otherFeesDetailsData) {
                    otherFeesDetails = JSON.parse(decodeURIComponent(otherFeesDetailsData));
                }
            } catch(e) {
                console.error('Error parsing other fees details:', e);
                otherFeesDetails = [];
            }

            var html = '';
            
            // Header
            html += '<div class="mb-4">';
            html += '<h6 class="text-primary-custom mb-2"><i class="bi bi-person"></i> Student: <strong>' + studentName + '</strong></h6>';
            html += '<h6 class="mb-0">';
            if (feeType === 'Tuition Fees') {
                html += '<span class="badge bg-success"><i class="bi bi-book"></i> ' + feeType + '</span>';
            } else {
                html += '<span class="badge bg-warning text-dark"><i class="bi bi-wallet2"></i> ' + feeType + '</span>';
            }
            html += '</h6>';
            html += '</div>';

            // Installments Section
            if (installments && installments.length > 0) {
                html += '<div class="card mb-3 border-success">';
                html += '<div class="card-header bg-success text-white">';
                html += '<h6 class="mb-0"><i class="bi bi-calendar-range"></i> Payment Installments</h6>';
                html += '</div>';
                html += '<div class="card-body">';
                html += '<div class="table-responsive">';
                html += '<table class="table table-bordered table-hover table-sm">';
                html += '<thead class="table-light">';
                html += '<tr><th>Installment</th><th>Type</th><th>Amount (TZS)</th></tr>';
                html += '</thead>';
                html += '<tbody>';

                var installmentTotal = 0;
                installments.forEach(function(installment) {
                    installmentTotal += parseFloat(installment.amount || 0);
                    html += '<tr>';
                    html += '<td><strong>' + (installment.installment_name || 'N/A') + '</strong></td>';
                    html += '<td><span class="badge bg-info">' + (installment.installment_type || 'N/A') + '</span></td>';
                    html += '<td class="text-success fw-bold">TZS ' + parseFloat(installment.amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                    html += '</tr>';
                });

                html += '</tbody>';
                html += '<tfoot class="table-light">';
                html += '<tr><th colspan="2">Total</th><th class="text-primary">TZS ' + installmentTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</th></tr>';
                html += '</tfoot>';
                html += '</table>';
                html += '</div>';
                
                // Partial Payment Info
                html += '<div class="mt-2">';
                html += '<small class="text-muted">';
                if (allowPartial) {
                    html += '<i class="bi bi-check-circle text-success"></i> Partial payments are allowed';
                } else {
                    html += '<i class="bi bi-x-circle text-danger"></i> Full installment amount must be paid (no partial payments)';
                }
                html += '</small>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
            } else {
                html += '<div class="card mb-3 border-warning">';
                html += '<div class="card-body">';
                html += '<div class="alert alert-warning mb-0">';
                html += '<i class="bi bi-exclamation-triangle"></i> <strong>Full Payment Required</strong><br>';
                html += 'This fee must be paid in full. No installment plan is available.';
                html += '</div>';
                html += '</div>';
                html += '</div>';
            }

            // Other Fees Details Section (only for Other Fees)
            if (feeType === 'Other Fees' && otherFeesDetails && otherFeesDetails.length > 0) {
                html += '<div class="card mb-3 border-warning">';
                html += '<div class="card-header bg-warning text-dark">';
                html += '<h6 class="mb-0"><i class="bi bi-list-ul"></i> Other Fees Breakdown</h6>';
                html += '</div>';
                html += '<div class="card-body">';
                html += '<div class="table-responsive">';
                html += '<table class="table table-bordered table-hover table-sm">';
                html += '<thead class="table-light">';
                html += '<tr><th>Item Name</th><th>Description</th><th>Amount (TZS)</th></tr>';
                html += '</thead>';
                html += '<tbody>';

                var otherFeesTotal = 0;
                otherFeesDetails.forEach(function(detail) {
                    otherFeesTotal += parseFloat(detail.amount || 0);
                    html += '<tr>';
                    html += '<td><strong>' + (detail.fee_detail_name || 'N/A') + '</strong></td>';
                    html += '<td>' + (detail.description || '-') + '</td>';
                    html += '<td class="text-warning fw-bold">TZS ' + parseFloat(detail.amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                    html += '</tr>';
                });

                html += '</tbody>';
                html += '<tfoot class="table-light">';
                html += '<tr><th colspan="2">Total Other Fees</th><th class="text-primary">TZS ' + otherFeesTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</th></tr>';
                html += '</tfoot>';
                html += '</table>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
            }

            $('#viewFeeDetailsContent').html(html);
            $('#viewFeeDetailsModal').modal('show');
        });

        // Load initial data
        loadFeesSummary();
    }

    // Initialize when DOM and jQuery are ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initFeesSummary, 200);
        });
    } else {
        setTimeout(initFeesSummary, 200);
    }
})();
</script>

