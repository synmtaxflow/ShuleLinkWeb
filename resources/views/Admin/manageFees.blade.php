@if($user_type == 'Admin')
@include('includes.Admin_nav')
@else
@include('includes.teacher_nav')
@endif

@php
    // Get school data for export
    use Illuminate\Support\Facades\Session;
    $schoolID = Session::get('schoolID');
    $school = \App\Models\School::where('schoolID', $schoolID)->first();
    $schoolName = $school ? $school->school_name : 'School Name';
    $schoolLogo = $school && $school->school_logo ? asset($school->school_logo) : '';
@endphp

<style>
    /* Color scheme for #940000 */
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
    .widget-card {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
        border-radius: 10px;
    }
    .widget-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(148, 0, 0, 0.2) !important;
    }
    .fee-widget {
        border-left: 4px solid #940000;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    }
    .fee-widget.tuition {
        border-left-color: #28a745;
    }
    .fee-widget.other {
        border-left-color: #ffc107;
    }
    .fee-amount {
        font-size: 1.8rem;
        font-weight: 700;
        color: #940000;
    }
    .fee-type-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
    }
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    .loading-overlay.show {
        display: flex;
    }
    .loading-spinner {
        background: white;
        padding: 2rem;
        border-radius: 10px;
        text-align: center;
    }
    /* Table Styling Improvements */
    .table-bordered {
        border: 2px solid #dee2e6 !important;
    }
    .table-bordered thead th {
        border-bottom: 2px solid #dee2e6 !important;
        font-weight: 700;
        color: #fff;
        text-transform: uppercase;
        font-size: 0.9rem;
        padding: 12px 15px;
    }
    .table-bordered tbody td {
        border: 1px solid #dee2e6;
        padding: 12px 15px;
        vertical-align: middle;
    }
    .table-bordered tfoot td {
        border-top: 2px solid #dee2e6 !important;
        padding: 15px;
        font-size: 1.05rem;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s;
    }
    .table-success thead th {
        background-color: #28a745 !important;
        color: #fff;
    }
    .table-warning thead th {
        background-color: #ffc107 !important;
        color: #000;
    }
    .border-end {
        border-right: 1px solid #dee2e6 !important;
    }
    .table tbody tr.border-top {
        border-top: 3px solid #ffc107 !important;
    }
    /* New Table Structure Styling */
    .table-primary thead th {
        background-color: #940000 !important;
        color: #fff;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.95rem;
        padding: 15px;
    }
    .table tbody tr {
        transition: background-color 0.2s;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .table tbody td {
        vertical-align: middle;
        padding: 15px;
    }
    .table tfoot td {
        font-size: 1.1rem;
        padding: 15px;
    }
    /* Fee breakdown styling */
    .fee-breakdown-item {
        padding: 8px 0;
        border-bottom: 1px solid #e9ecef;
    }
    .fee-breakdown-item:last-child {
        border-bottom: none;
    }
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- jsPDF for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<!-- SheetJS for Excel export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

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
                            <i class="bi bi-cash-stack"></i> Manage Fees
                        </h4>
                        <div class="d-flex gap-2">
                            <button class="btn btn-light text-primary-custom fw-bold" type="button" id="exportPdfBtn">
                                <i class="bi bi-file-pdf"></i> Export PDF
                            </button>
                            <button class="btn btn-light text-primary-custom fw-bold" type="button" id="exportExcelBtn">
                                <i class="bi bi-file-excel"></i> Export Excel
                            </button>
                            <a href="{{ route('view_payments') }}" class="btn btn-light text-primary-custom fw-bold">
                                <i class="bi bi-credit-card"></i> View Payments & Control Numbers
                            </a>
                            <button class="btn btn-light text-primary-custom fw-bold" type="button" id="openAddFeeModalBtn">
                                <i class="bi bi-plus-circle"></i> Assign Fee to Class
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fees Structure Table -->
            @if(count($classes) > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0" style="border-collapse: separate; border-spacing: 0;">
                                <thead class="table-primary" style="background-color: #940000 !important;">
                                    <tr>
                                        <th style="width: 12%;" class="text-center border-end text-white">Class</th>
                                        <th style="width: 30%;" class="border-end text-white">Tuition Fee (TZS)</th>
                                        <th style="width: 35%;" class="border-end text-white">Other Fee (TZS)</th>
                                        <th style="width: 23%;" class="text-end border-end text-white">Total Fee (TZS)</th>
                                    </tr>
                                </thead>
                                <tbody>
                @foreach($classes as $class)
                    @php
                        $classFees = $feesByClass->get($class->classID, collect());
                        $tuitionFees = $classFees->where('fee_type', 'Tuition Fees')->where('status', 'Active');
                        $otherFees = $classFees->where('fee_type', 'Other Fees')->where('status', 'Active');
                        $totalTuition = $tuitionFees->sum('amount');
                        $totalOther = $otherFees->sum('amount');
                                            $totalFee = $totalTuition + $totalOther;
                                            // Get first tuition fee and first other fee for display
                                            $tuitionFee = $tuitionFees->first();
                                            $otherFee = $otherFees->first();
                    @endphp
                    
                                        <tr class="align-middle">
                                            <!-- Class Column -->
                                            <td class="text-center border-end align-middle">
                                                <strong class="text-dark" style="font-size: 1.1rem;">
                                                    <i class="bi bi-mortarboard text-primary-custom"></i> {{ $class->class_name }}
                                                </strong>
                                                                    </td>
                                            
                                            <!-- Tuition Fee Column -->
                                            <td class="border-end align-middle">
                                                @if($tuitionFee)
                                                    <div>
                                                        <div class="mb-2">
                                                            <strong class="text-success" style="font-size: 1.1rem;">
                                                                {{ number_format($tuitionFee->amount, 0) }}/=
                                                            </strong>
                                                        </div>
                                                        <hr class="my-2">
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <button class="btn btn-sm btn-outline-info view-installment-btn" 
                                                                    data-fee-id="{{ $tuitionFee->feeID }}"
                                                                    data-fee-type="{{ $tuitionFee->fee_type }}"
                                                                    title="View Installments">
                                                                <i class="bi bi-calendar-range"></i>
                                                                            </button>
                                                                            <button class="btn btn-sm btn-outline-primary edit-fee-btn" 
                                                                    data-fee-id="{{ $tuitionFee->feeID }}"
                                                                    title="Edit Fee">
                                                                                <i class="bi bi-pencil"></i>
                                                                            </button>
                                                                            <button class="btn btn-sm btn-outline-danger delete-fee-btn" 
                                                                    data-fee-id="{{ $tuitionFee->feeID }}"
                                                                    data-fee-name="{{ $tuitionFee->fee_name }}"
                                                                    title="Delete Fee">
                                                                                <i class="bi bi-trash"></i>
                                                                            </button>
                                                                        </div>
                                                </div>
                                            @else
                                                    <span class="text-muted">-</span>
                                            @endif
                                            </td>
                                            
                                            <!-- Other Fee Column -->
                                            <td class="border-end align-middle">
                                                @if($otherFee)
                                                    @php 
                                                        $otherFeeDetails = $otherFee->otherFeeDetails->where('status', 'Active');
                                                                    $hasDetails = $otherFeeDetails->count() > 0;
                                                                @endphp
                                                    <div>
                                                                @if($hasDetails)
                                                            <div class="mb-2">
                                                                @foreach($otherFeeDetails as $detail)
                                                                    <div class="mb-1">
                                                                        <div class="d-flex justify-content-between align-items-start">
                                                                            <span class="text-muted small">
                                                                                <i class="bi bi-dot"></i> {{ $detail->fee_detail_name }}
                                                                                    </span>
                                                                            <span class="text-warning small ms-2">
                                                                                {{ number_format($detail->amount, 0) }}/=
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                                <div class="mt-2 pt-2 border-top">
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <strong class="text-dark small">Total:</strong>
                                                                        <strong class="text-warning">
                                                                            {{ number_format($otherFee->amount, 0) }}/=
                                                                        </strong>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div class="mb-2">
                                                                <strong class="text-warning" style="font-size: 1.1rem;">
                                                                    {{ number_format($otherFee->amount, 0) }}/=
                                                                </strong>
                                                            </div>
                                                                            @endif
                                                        <hr class="my-2">
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <button class="btn btn-sm btn-outline-info view-installment-btn" 
                                                                    data-fee-id="{{ $otherFee->feeID }}"
                                                                    data-fee-type="{{ $otherFee->fee_type }}"
                                                                    title="View Installments">
                                                                <i class="bi bi-calendar-range"></i>
                                                                                        </button>
                                                                                        <button class="btn btn-sm btn-outline-primary edit-fee-btn" 
                                                                    data-fee-id="{{ $otherFee->feeID }}"
                                                                    title="Edit Fee">
                                                                                            <i class="bi bi-pencil"></i>
                                                                                        </button>
                                                                                        <button class="btn btn-sm btn-outline-danger delete-fee-btn" 
                                                                    data-fee-id="{{ $otherFee->feeID }}"
                                                                    data-fee-name="{{ $otherFee->fee_name }}"
                                                                    title="Delete Fee">
                                                                                            <i class="bi bi-trash"></i>
                                                                                        </button>
                                                                                    </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                                            @endif
                                            </td>
                                            
                                            <!-- Total Fee Column -->
                                            <td class="text-end border-end align-middle">
                                                <strong class="text-primary-custom" style="font-size: 1.3rem;">
                                                    {{ number_format($totalFee, 0) }}/=
                                                </strong>
                                                                        </td>
                                                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td class="text-center fw-bold border-end">
                                            <strong>TOTAL</strong>
                                                                        </td>
                                        <td class="text-end border-end">
                                            <strong class="text-success" style="font-size: 1.2rem;">
                                                {{ number_format($classes->sum(function($class) use ($feesByClass) {
                                                    $classFees = $feesByClass->get($class->classID, collect());
                                                    return $classFees->where('fee_type', 'Tuition Fees')->where('status', 'Active')->sum('amount');
                                                }), 0) }}/=
                                            </strong>
                                                                        </td>
                                        <td class="text-end border-end">
                                            <strong class="text-warning" style="font-size: 1.2rem;">
                                                {{ number_format($classes->sum(function($class) use ($feesByClass) {
                                                    $classFees = $feesByClass->get($class->classID, collect());
                                                    return $classFees->where('fee_type', 'Other Fees')->where('status', 'Active')->sum('amount');
                                                }), 0) }}/=
                                            </strong>
                                                                        </td>
                                        <td class="text-end border-end">
                                            <strong class="text-primary-custom" style="font-size: 1.3rem;">
                                                {{ number_format($classes->sum(function($class) use ($feesByClass) {
                                                    $classFees = $feesByClass->get($class->classID, collect());
                                                    $tuition = $classFees->where('fee_type', 'Tuition Fees')->where('status', 'Active')->sum('amount');
                                                    $other = $classFees->where('fee_type', 'Other Fees')->where('status', 'Active')->sum('amount');
                                                    return $tuition + $other;
                                                }), 0) }}/=
                                            </strong>
                                                                        </td>
                                                                    </tr>
                                </tfoot>
                                                    </table>
                                                </div>
                                                </div>
                                        </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 64px; color: #940000;"></i>
                        <p class="mt-3 mb-0 text-muted">No classes found. Please create classes first.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Fee Modal -->
<div class="modal fade" id="addFeeModal" tabindex="-1" aria-labelledby="addFeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addFeeModalLabel">
                    <i class="bi bi-plus-circle"></i> Assign Fee to Class
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addFeeForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Class <span class="text-danger">*</span></label>
                        <select name="classID" id="fee_class_select" class="form-select" required>
                            <option value="">Choose a class...</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fee Type <span class="text-danger">*</span></label>
                        <select name="fee_type" id="fee_type_select" class="form-select" required>
                            <option value="">Choose fee type...</option>
                            <option value="Tuition Fees">Tuition Fees</option>
                            <option value="Other Fees">Other Fees</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fee Name <span class="text-danger">*</span></label>
                        <input type="text" name="fee_name" class="form-control" placeholder="e.g., School Fees, Library Fee, Sports Fee" required maxlength="200">
                    </div>
                    
                    <!-- Other Fees Details Section -->
                    <div id="other_fees_details_section" style="display: none;">
                        <div class="card border-warning mb-3">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="bi bi-list-ul"></i> Other Fees Details
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Add individual items that make up the Other Fees (e.g., Food, Study Tour, Library, etc.). The total will be calculated automatically.</p>
                                
                                <div id="other_fees_details_list">
                                    <!-- Other fee details will be added here dynamically -->
                                </div>
                                
                                <button type="button" class="btn btn-sm btn-outline-primary" id="add_other_fee_detail_btn">
                                    <i class="bi bi-plus-circle"></i> Add Other Fee Item
                                </button>
                                
                                <div class="mt-3 border-top pt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>Total Other Fees:</strong>
                                        <strong class="text-primary" id="other_fees_total">TZS 0.00</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount (TZS) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="fee_amount_input" class="form-control" placeholder="0.00" step="0.01" min="0" required>
                        <small class="text-muted" id="amount_help_text">Total fee amount</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Duration <span class="text-danger">*</span></label>
                        <select name="duration" id="fee_duration_select" class="form-select" required>
                            <option value="">Choose duration...</option>
                            <option value="Year" selected>Year</option>
                            <option value="Month">Month</option>
                            <option value="Term">Term</option>
                            <option value="Semester">Semester</option>
                            <option value="One-time">One-time</option>
                        </select>
                        <small class="text-muted">Default duration for this fee (usually Year for annual fees)</small>
                    </div>
                    
                    <!-- Installment Options -->
                    <div class="card border-primary-custom mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-calendar-range"></i> Payment Installment Options
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allow_partial_payment" id="allow_partial_payment" value="1" checked>
                                    <label class="form-check-label fw-bold" for="allow_partial_payment">
                                        Allow Partial Payment
                                    </label>
                                </div>
                                <small class="text-muted">If unchecked, parents must pay the full installment amount, not partial amounts</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allow_installments" id="allow_installments" value="1">
                                    <label class="form-check-label fw-bold" for="allow_installments">
                                        Allow Payment by Installments
                                    </label>
                                </div>
                                <small class="text-muted">Parents can pay this fee in installments (semester, monthly, etc.)</small>
                            </div>
                            
                            <div id="installment_options" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Installment Type <span class="text-danger">*</span></label>
                                    <select name="default_installment_type" id="default_installment_type" class="form-select">
                                        <option value="">Select installment type...</option>
                                        <option value="Semester">Semester</option>
                                        <option value="Month">Monthly</option>
                                        <option value="Two Months">Bi-Monthly (Every 2 Months)</option>
                                        <option value="Term">Term</option>
                                        <option value="Quarter">Quarterly</option>
                                        <option value="One-time">One-time</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Number of Installments <span class="text-danger">*</span></label>
                                    <input type="number" name="number_of_installments" id="number_of_installments" class="form-control" min="1" max="12" placeholder="e.g., 2 for 2 semesters, 12 for 12 months">
                                    <small class="text-muted">Total number of installments (e.g., 2 for 2 semesters, 12 for 12 months)</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Installment Amounts</label>
                                    <div id="installment_amounts_container" class="border rounded p-3 bg-light">
                                        <small class="text-muted">Installment amounts will be calculated automatically based on total fee amount and number of installments.</small>
                                    </div>
                                    <div id="installment_total_validation" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Optional description about this fee"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Assign Fee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Fee Modal -->
<div class="modal fade" id="editFeeModal" tabindex="-1" aria-labelledby="editFeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="editFeeModalLabel">
                    <i class="bi bi-pencil"></i> Edit Fee
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editFeeForm">
                @csrf
                <input type="hidden" name="feeID" id="edit_fee_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Class <span class="text-danger">*</span></label>
                        <select name="classID" id="edit_fee_class_select" class="form-select" required>
                            <option value="">Choose a class...</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fee Type <span class="text-danger">*</span></label>
                        <select name="fee_type" id="edit_fee_type_select" class="form-select" required>
                            <option value="">Choose fee type...</option>
                            <option value="Tuition Fees">Tuition Fees</option>
                            <option value="Other Fees">Other Fees</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fee Name <span class="text-danger">*</span></label>
                        <input type="text" name="fee_name" id="edit_fee_name" class="form-control" placeholder="e.g., School Fees, Library Fee, Sports Fee" required maxlength="200">
                    </div>
                    
                    <!-- Other Fees Details Section for Edit -->
                    <div id="edit_other_fees_details_section" style="display: none;">
                        <div class="card border-warning mb-3">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="bi bi-list-ul"></i> Other Fees Details
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Add individual items that make up the Other Fees (e.g., Food, Study Tour, Library, etc.). The total will be calculated automatically.</p>
                                
                                <div id="edit_other_fees_details_list">
                                    <!-- Other fee details will be added here dynamically -->
                                </div>
                                
                                <button type="button" class="btn btn-sm btn-outline-primary" id="add_edit_other_fee_detail_btn">
                                    <i class="bi bi-plus-circle"></i> Add Other Fee Item
                                </button>
                                
                                <div class="mt-3 border-top pt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>Total Other Fees:</strong>
                                        <strong class="text-primary" id="edit_other_fees_total">TZS 0.00</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount (TZS) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="edit_fee_amount" class="form-control" placeholder="0.00" step="0.01" min="0" required>
                        <small class="text-muted" id="edit_amount_help_text">Total fee amount</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Duration <span class="text-danger">*</span></label>
                        <select name="duration" id="edit_fee_duration_select" class="form-select" required>
                            <option value="">Choose duration...</option>
                            <option value="Year">Year</option>
                            <option value="Month">Month</option>
                            <option value="Term">Term</option>
                            <option value="Semester">Semester</option>
                            <option value="One-time">One-time</option>
                        </select>
                        <small class="text-muted">Default duration for this fee (usually Year for annual fees)</small>
                    </div>
                    
                    <!-- Installment Options -->
                    <div class="card border-primary-custom mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-calendar-range"></i> Payment Installment Options
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allow_partial_payment" id="edit_allow_partial_payment" value="1">
                                    <label class="form-check-label fw-bold" for="edit_allow_partial_payment">
                                        Allow Partial Payment
                                    </label>
                                </div>
                                <small class="text-muted">If unchecked, parents must pay the full installment amount, not partial amounts</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allow_installments" id="edit_allow_installments" value="1">
                                    <label class="form-check-label fw-bold" for="edit_allow_installments">
                                        Allow Payment by Installments
                                    </label>
                                </div>
                                <small class="text-muted">Parents can pay this fee in installments (semester, monthly, etc.)</small>
                            </div>
                            
                            <div id="edit_installment_options" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Installment Type <span class="text-danger">*</span></label>
                                    <select name="default_installment_type" id="edit_default_installment_type" class="form-select">
                                        <option value="">Select installment type...</option>
                                        <option value="Semester">Semester</option>
                                        <option value="Month">Monthly</option>
                                        <option value="Two Months">Bi-Monthly (Every 2 Months)</option>
                                        <option value="Term">Term</option>
                                        <option value="Quarter">Quarterly</option>
                                        <option value="One-time">One-time</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Number of Installments <span class="text-danger">*</span></label>
                                    <input type="number" name="number_of_installments" id="edit_number_of_installments" class="form-control" min="1" max="12" placeholder="e.g., 2 for 2 semesters, 12 for 12 months">
                                    <small class="text-muted">Total number of installments (e.g., 2 for 2 semesters, 12 for 12 months)</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Installment Amounts</label>
                                    <div id="edit_installment_amounts_container" class="border rounded p-3 bg-light">
                                        <small class="text-muted">Installment amounts will be calculated automatically based on total fee amount and number of installments.</small>
                                    </div>
                                    <div id="edit_installment_total_validation" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" id="edit_fee_description" class="form-control" rows="3" placeholder="Optional description about this fee"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Update Fee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Fee Details Modal -->
<div class="modal fade" id="viewFeeModal" tabindex="-1" aria-labelledby="viewFeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewFeeModalLabel">
                    <i class="bi bi-eye"></i> Fee Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewFeeContent">
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

<!-- View Installments Modal -->
<div class="modal fade" id="viewInstallmentModal" tabindex="-1" aria-labelledby="viewInstallmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 800px;">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="viewInstallmentModalLabel">
                    <i class="bi bi-calendar-range"></i> Installments - <span id="installmentClassName"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewInstallmentContent">
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

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <div class="spinner-border text-primary-custom" role="status" style="width: 3rem; height: 3rem;">
            <span class="sr-only">Loading...</span>
        </div>
        <p class="mt-3 mb-0 fw-bold text-primary-custom">Inasave...</p>
    </div>
</div>

@include('includes.footer')

<script>
// Wait for jQuery and Bootstrap to be loaded
(function() {
    function initFeesManagement() {
        // Check if jQuery is loaded
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded!');
            setTimeout(initFeesManagement, 100);
            return;
        }
        
        // Use jQuery
        var $ = jQuery;
        
        // CSRF Token setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Open Add Fee Modal Button Click Handler
        $(document).on('click', '#openAddFeeModalBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Open modal button clicked');
            
            // Check if modal exists
            if ($('#addFeeModal').length === 0) {
                console.error('Modal #addFeeModal not found!');
                alert('Modal haijapatikana. Tafadhali angalia console kwa maelezo zaidi.');
                return;
            }
            
            // Try to show modal
            try {
                $('#addFeeModal').modal('show');
                console.log('Modal show command executed');
            } catch (error) {
                console.error('Error showing modal:', error);
                alert('Kosa la kuonyesha modal: ' + error.message);
            }
        });

        // Also handle data-toggle for backward compatibility
        $(document).on('click', '[data-toggle="modal"][data-target="#addFeeModal"]', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Modal opened via data-toggle');
            $('#addFeeModal').modal('show');
        });
        
        // Test if button exists
        console.log('Button exists:', $('#openAddFeeModalBtn').length > 0);
        console.log('Modal exists:', $('#addFeeModal').length > 0);
        console.log('jQuery version:', $.fn.jquery);

        // Show/hide installment options based on checkbox - only if partial payment is allowed
        function toggleInstallmentOptions() {
            const allowPartial = $('#allow_partial_payment').is(':checked');
            const allowInstallments = $('#allow_installments').is(':checked');
            
            if (!allowPartial) {
                // If partial payment is not allowed, disable and hide installments
                $('#allow_installments').prop('checked', false);
                $('#installment_options').slideUp();
            } else if (allowInstallments) {
                $('#installment_options').slideDown();
                calculateInstallments();
            } else {
                $('#installment_options').slideUp();
            }
        }

        function toggleEditInstallmentOptions() {
            const allowPartial = $('#edit_allow_partial_payment').is(':checked');
            const allowInstallments = $('#edit_allow_installments').is(':checked');
            
            if (!allowPartial) {
                // If partial payment is not allowed, disable and hide installments
                $('#edit_allow_installments').prop('checked', false);
                $('#edit_installment_options').slideUp();
            } else if (allowInstallments) {
                $('#edit_installment_options').slideDown();
                calculateEditInstallments();
            } else {
                $('#edit_installment_options').slideUp();
            }
        }

        $('#allow_partial_payment').on('change', toggleInstallmentOptions);
        $('#allow_installments').on('change', toggleInstallmentOptions);
        
        $('#edit_allow_partial_payment').on('change', toggleEditInstallmentOptions);
        $('#edit_allow_installments').on('change', toggleEditInstallmentOptions);

        // Calculate and display installment amounts
        function calculateInstallments() {
            const totalAmount = parseFloat($('input[name="amount"]').val()) || 0;
            const numberOfInstallments = parseInt($('#number_of_installments').val()) || 0;
            const installmentType = $('#default_installment_type').val();
            
            if (totalAmount > 0 && numberOfInstallments > 0 && installmentType) {
                const container = $('#installment_amounts_container');
                let html = '<div class="table-responsive"><table class="table table-sm table-bordered mb-0">';
                html += '<thead><tr><th>Installment</th><th>Amount (TZS)</th></tr></thead><tbody>';
                
                let totalCalculated = 0;
                const baseAmount = totalAmount / numberOfInstallments;
                
                for (let i = 1; i <= numberOfInstallments; i++) {
                    let installmentName = '';
                    switch(installmentType) {
                        case 'Semester': installmentName = 'Semester ' + i; break;
                        case 'Month': installmentName = 'Month ' + i; break;
                        case 'Two Months': installmentName = 'Two Months ' + i; break;
                        case 'Term': installmentName = 'Term ' + i; break;
                        case 'Quarter': installmentName = 'Quarter ' + i; break;
                        case 'One-time': installmentName = 'One-time Payment'; break;
                        default: installmentName = 'Installment ' + i;
                    }
                    
                    // For the last installment, add any remainder to ensure total matches
                    let installmentAmount = baseAmount;
                    if (i === numberOfInstallments) {
                        installmentAmount = totalAmount - totalCalculated;
                    } else {
                        installmentAmount = Math.floor(baseAmount * 100) / 100; // Round to 2 decimals
                    }
                    
                    totalCalculated += installmentAmount;
                    html += `<tr><td><strong>${installmentName}</strong></td><td>TZS ${installmentAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td></tr>`;
                }
                
                html += '</tbody></table></div>';
                html += `<div class="mt-2"><strong>Total: TZS ${totalCalculated.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></div>`;
                
                container.html(html);
                
                // Validate total
                const difference = Math.abs(totalCalculated - totalAmount);
                const validationDiv = $('#installment_total_validation');
                
                if (difference < 0.01) { // Allow small floating point differences
                    validationDiv.html('<div class="alert alert-success mb-0 py-2"><i class="bi bi-check-circle"></i> Installment total matches fee amount</div>');
                } else {
                    validationDiv.html('<div class="alert alert-danger mb-0 py-2"><i class="bi bi-exclamation-triangle"></i> Warning: Installment total does not match fee amount</div>');
                }
            } else {
                $('#installment_amounts_container').html('<small class="text-muted">Enter fee amount, installment type, and number of installments to calculate amounts.</small>');
                $('#installment_total_validation').html('');
            }
        }

        function calculateEditInstallments() {
            const totalAmount = parseFloat($('#edit_fee_amount').val()) || 0;
            const numberOfInstallments = parseInt($('#edit_number_of_installments').val()) || 0;
            const installmentType = $('#edit_default_installment_type').val();
            
            if (totalAmount > 0 && numberOfInstallments > 0 && installmentType) {
                const container = $('#edit_installment_amounts_container');
                let html = '<div class="table-responsive"><table class="table table-sm table-bordered mb-0">';
                html += '<thead><tr><th>Installment</th><th>Amount (TZS)</th></tr></thead><tbody>';
                
                let totalCalculated = 0;
                const baseAmount = totalAmount / numberOfInstallments;
                
                for (let i = 1; i <= numberOfInstallments; i++) {
                    let installmentName = '';
                    switch(installmentType) {
                        case 'Semester': installmentName = 'Semester ' + i; break;
                        case 'Month': installmentName = 'Month ' + i; break;
                        case 'Two Months': installmentName = 'Two Months ' + i; break;
                        case 'Term': installmentName = 'Term ' + i; break;
                        case 'Quarter': installmentName = 'Quarter ' + i; break;
                        case 'One-time': installmentName = 'One-time Payment'; break;
                        default: installmentName = 'Installment ' + i;
                    }
                    
                    // For the last installment, add any remainder to ensure total matches
                    let installmentAmount = baseAmount;
                    if (i === numberOfInstallments) {
                        installmentAmount = totalAmount - totalCalculated;
                    } else {
                        installmentAmount = Math.floor(baseAmount * 100) / 100; // Round to 2 decimals
                    }
                    
                    totalCalculated += installmentAmount;
                    html += `<tr><td><strong>${installmentName}</strong></td><td>TZS ${installmentAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td></tr>`;
                }
                
                html += '</tbody></table></div>';
                html += `<div class="mt-2"><strong>Total: TZS ${totalCalculated.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></div>`;
                
                container.html(html);
                
                // Validate total
                const difference = Math.abs(totalCalculated - totalAmount);
                const validationDiv = $('#edit_installment_total_validation');
                
                if (difference < 0.01) { // Allow small floating point differences
                    validationDiv.html('<div class="alert alert-success mb-0 py-2"><i class="bi bi-check-circle"></i> Installment total matches fee amount</div>');
                } else {
                    validationDiv.html('<div class="alert alert-danger mb-0 py-2"><i class="bi bi-exclamation-triangle"></i> Warning: Installment total does not match fee amount</div>');
                }
            } else {
                $('#edit_installment_amounts_container').html('<small class="text-muted">Enter fee amount, installment type, and number of installments to calculate amounts.</small>');
                $('#edit_installment_total_validation').html('');
            }
        }

        // Show/hide other fees details section based on fee type
        $('#fee_type_select').on('change', function() {
            if ($(this).val() === 'Other Fees') {
                $('#other_fees_details_section').slideDown();
                $('#amount_help_text').text('Total will be calculated from Other Fees Details below');
            } else {
                $('#other_fees_details_section').slideUp();
                $('#amount_help_text').text('Total fee amount');
                // Clear other fees details
                $('#other_fees_details_list').empty();
                updateOtherFeesTotal();
            }
        });

        // Add Other Fee Detail
        let otherFeeDetailCounter = 0;
        $(document).on('click', '#add_other_fee_detail_btn', function() {
            otherFeeDetailCounter++;
            const detailHtml = `
                <div class="other-fee-detail-item border rounded p-3 mb-3" data-detail-index="${otherFeeDetailCounter}">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm other-fee-detail-name" placeholder="e.g., Food, Study Tour, Library" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Amount (TZS) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm other-fee-detail-amount" placeholder="0.00" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">&nbsp;</label>
                            <button type="button" class="btn btn-sm btn-danger w-100 remove-other-fee-detail" title="Remove">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Description (Optional)</label>
                            <textarea class="form-control form-control-sm other-fee-detail-description" rows="2" placeholder="Optional description"></textarea>
                        </div>
                    </div>
                </div>
            `;
            $('#other_fees_details_list').append(detailHtml);
            updateOtherFeesTotal();
        });

        // Remove Other Fee Detail
        $(document).on('click', '.remove-other-fee-detail', function() {
            $(this).closest('.other-fee-detail-item').remove();
            updateOtherFeesTotal();
        });

        // Add Edit Other Fee Detail
        let editOtherFeeDetailCounter = 0;
        $(document).on('click', '#add_edit_other_fee_detail_btn', function() {
            editOtherFeeDetailCounter++;
            const detailHtml = `
                <div class="edit-other-fee-detail-item border rounded p-3 mb-3" data-detail-index="${editOtherFeeDetailCounter}">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm edit-other-fee-detail-name" placeholder="e.g., Food, Study Tour, Library" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Amount (TZS) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm edit-other-fee-detail-amount" placeholder="0.00" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">&nbsp;</label>
                            <button type="button" class="btn btn-sm btn-danger w-100 remove-edit-other-fee-detail" title="Remove">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Description (Optional)</label>
                            <textarea class="form-control form-control-sm edit-other-fee-detail-description" rows="2" placeholder="Optional description"></textarea>
                        </div>
                    </div>
                </div>
            `;
            $('#edit_other_fees_details_list').append(detailHtml);
            updateEditOtherFeesTotal();
        });

        // Remove Edit Other Fee Detail
        $(document).on('click', '.remove-edit-other-fee-detail', function() {
            $(this).closest('.edit-other-fee-detail-item').remove();
            updateEditOtherFeesTotal();
        });

        // Update total when other fee detail amounts change
        $(document).on('input', '.other-fee-detail-amount', function() {
            updateOtherFeesTotal();
        });

        $(document).on('input', '.edit-other-fee-detail-amount', function() {
            updateEditOtherFeesTotal();
        });

        // Function to calculate and update edit other fees total
        function updateEditOtherFeesTotal() {
            let total = 0;
            $('.edit-other-fee-detail-amount').each(function() {
                const amount = parseFloat($(this).val()) || 0;
                total += amount;
            });
            
            $('#edit_other_fees_total').text('TZS ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            
            // Update the main amount field if fee type is Other Fees
            if ($('#edit_fee_type_select').val() === 'Other Fees') {
                $('#edit_fee_amount').val(total.toFixed(2));
                calculateEditInstallments();
            }
        }

        // Function to calculate and update other fees total
        function updateOtherFeesTotal() {
            let total = 0;
            $('.other-fee-detail-amount').each(function() {
                const amount = parseFloat($(this).val()) || 0;
                total += amount;
            });
            
            $('#other_fees_total').text('TZS ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            
            // Update the main amount field if fee type is Other Fees
            if ($('#fee_type_select').val() === 'Other Fees') {
                $('#fee_amount_input').val(total.toFixed(2));
                calculateInstallments();
            }
        }

        // Real-time calculation on input change
        $('input[name="amount"]').on('input', calculateInstallments);
        $('#number_of_installments').on('input', calculateInstallments);
        $('#default_installment_type').on('change', calculateInstallments);
        
        $('#edit_fee_amount').on('input', calculateEditInstallments);
        $('#edit_number_of_installments').on('input', calculateEditInstallments);
        $('#edit_default_installment_type').on('change', calculateEditInstallments);

    // Add Fee Form Submission
    $('#addFeeForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate installment amounts match total fee
        const totalAmount = parseFloat($('input[name="amount"]').val()) || 0;
        const allowInstallments = $('#allow_installments').is(':checked');
        const numberOfInstallments = parseInt($('#number_of_installments').val()) || 0;
        
        if (allowInstallments && numberOfInstallments > 0) {
            const calculatedTotal = (totalAmount / numberOfInstallments) * numberOfInstallments;
            const difference = Math.abs(calculatedTotal - totalAmount);
            
            if (difference > 0.01) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Installment amounts do not match the total fee amount. Please check your calculations.',
                    confirmButtonColor: '#940000'
                });
                return;
            }
        }
        
        // Collect other fees details if fee type is Other Fees
        let otherFeesDetails = [];
        if ($('#fee_type_select').val() === 'Other Fees') {
            $('.other-fee-detail-item').each(function() {
                const name = $(this).find('.other-fee-detail-name').val();
                const amount = $(this).find('.other-fee-detail-amount').val();
                const description = $(this).find('.other-fee-detail-description').val();
                
                if (name && amount) {
                    otherFeesDetails.push({
                        name: name.trim(),
                        amount: parseFloat(amount),
                        description: description ? description.trim() : null
                    });
                }
            });
            
            console.log('Other Fees Details Collected:', otherFeesDetails);
        }
        
        // Ensure checkbox values are properly set
        $('#allow_installments').val($('#allow_installments').is(':checked') ? 1 : 0);
        $('#allow_partial_payment').val($('#allow_partial_payment').is(':checked') ? 1 : 0);
        
        let formData = $(this).serialize();
        
        // Add other fees details to form data
        if (otherFeesDetails.length > 0) {
            formData += '&other_fees_details=' + encodeURIComponent(JSON.stringify(otherFeesDetails));
            console.log('Form Data with Other Fees:', formData);
        }
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        const modal = $('#addFeeModal');
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Inasave...');
        
        // Show loading overlay
        $('#loadingOverlay').addClass('show');
        
        // Disable all form inputs during submission
        modal.find('input, select, textarea').prop('disabled', true);
        
        console.log('Sending request to server...');
        
        $.ajax({
            url: '{{ route("store_fee") }}',
            method: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                console.log('Request sent, waiting for response...');
            },
            success: function(response) {
                console.log('Server response received:', response);
                
                if (response && response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Imefanikiwa!',
                        text: response.message || 'Ada imeassign kwa mafanikio',
                        timer: 2500,
                        showConfirmButton: true,
                        confirmButtonColor: '#940000'
                    }).then((result) => {
                        modal.modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tahadhari!',
                        text: response.message || 'Imesave lakini hakuna response ya uhakika',
                        confirmButtonColor: '#940000'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr, status, error);
                
                let errorMessage = 'Imeshindwa kuassign ada. Tafadhali jaribu tena.';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        // Handle validation errors
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('<br>');
                    }
                } else if (xhr.status === 0) {
                    errorMessage = 'Hakuna muunganisho na server. Tafadhali angalia muunganisho wako.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Kosa la server. Tafadhali wasiliana na msimamizi.';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Kosa!',
                    html: errorMessage,
                    confirmButtonColor: '#940000'
                });
            },
            complete: function() {
                console.log('Request completed');
                // Hide loading overlay
                $('#loadingOverlay').removeClass('show');
                // Re-enable form inputs
                modal.find('input, select, textarea').prop('disabled', false);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // View Fee Button Click
    $(document).on('click', '.view-fee-btn', function() {
        const feeID = $(this).data('fee-id');
        const feeType = $(this).data('fee-type');
        
        $.ajax({
            url: `/get_fee/${feeID}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const fee = response.fee;
                    let html = '';
                    
                    // Fee Basic Information
                    html += '<div class="card mb-3">';
                    html += '<div class="card-header bg-light">';
                    html += '<h6 class="mb-0"><i class="bi bi-info-circle"></i> Fee Information</h6>';
                    html += '</div>';
                    html += '<div class="card-body">';
                    html += '<div class="row">';
                    html += '<div class="col-md-6 mb-2"><strong>Fee Name:</strong> ' + (fee.fee_name || 'N/A') + '</div>';
                    html += '<div class="col-md-6 mb-2"><strong>Fee Type:</strong> <span class="badge bg-' + (fee.fee_type === 'Tuition Fees' ? 'success' : 'warning text-dark') + '">' + (fee.fee_type || 'N/A') + '</span></div>';
                    html += '<div class="col-md-6 mb-2"><strong>Total Amount:</strong> <span class="text-primary fw-bold">TZS ' + parseFloat(fee.amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</span></div>';
                    html += '<div class="col-md-6 mb-2"><strong>Duration:</strong> ' + (fee.duration || 'N/A') + '</div>';
                    if (fee.description) {
                        html += '<div class="col-12 mb-2"><strong>Description:</strong> ' + fee.description + '</div>';
                    }
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                    
                    // Installments Section (for both Tuition and Other Fees)
                    if (fee.allow_installments && fee.installments && fee.installments.length > 0) {
                        html += '<div class="card mb-3">';
                        html += '<div class="card-header bg-success text-white">';
                        html += '<h6 class="mb-0"><i class="bi bi-calendar-range"></i> Payment Installments</h6>';
                        html += '</div>';
                        html += '<div class="card-body">';
                        html += '<div class="table-responsive">';
                        html += '<table class="table table-bordered table-hover">';
                        html += '<thead class="table-light">';
                        html += '<tr><th>Installment</th><th>Type</th><th>Amount (TZS)</th></tr>';
                        html += '</thead>';
                        html += '<tbody>';
                        
                        let installmentTotal = 0;
                        fee.installments.forEach(function(installment) {
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
                        html += '<div class="mt-2">';
                        html += '<small class="text-muted">';
                        if (fee.allow_partial_payment) {
                            html += '<i class="bi bi-check-circle text-success"></i> Partial payments are allowed';
                        } else {
                            html += '<i class="bi bi-x-circle text-danger"></i> Full installment amount must be paid (no partial payments)';
                        }
                        html += '</small>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                    } else {
                        // No installments - show message
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
                    if (feeType === 'Other Fees' && fee.other_fee_details && fee.other_fee_details.length > 0) {
                        html += '<div class="card mb-3">';
                        html += '<div class="card-header bg-warning text-dark">';
                        html += '<h6 class="mb-0"><i class="bi bi-list-ul"></i> Other Fees Breakdown</h6>';
                        html += '</div>';
                        html += '<div class="card-body">';
                        html += '<div class="table-responsive">';
                        html += '<table class="table table-bordered table-hover">';
                        html += '<thead class="table-light">';
                        html += '<tr><th>Item Name</th><th>Description</th><th>Amount (TZS)</th></tr>';
                        html += '</thead>';
                        html += '<tbody>';
                        
                        let otherFeesTotal = 0;
                        fee.other_fee_details.forEach(function(detail) {
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
                    
                    $('#viewFeeContent').html(html);
                    $('#viewFeeModal').modal('show');
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to load fee details'
                });
            }
        });
    });

    // Edit Fee Button Click
    $(document).on('click', '.edit-fee-btn', function() {
        const feeID = $(this).data('fee-id');
        
        $.ajax({
            url: `/get_fee/${feeID}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const fee = response.fee;
                    $('#edit_fee_id').val(fee.feeID);
                    $('#edit_fee_class_select').val(fee.classID);
                    $('#edit_fee_type_select').val(fee.fee_type);
                    $('#edit_fee_name').val(fee.fee_name);
                    $('#edit_fee_amount').val(fee.amount);
                    $('#edit_fee_duration_select').val(fee.duration);
                    $('#edit_fee_description').val(fee.description || '');
                    
                    // Installment fields
                    if (fee.allow_installments) {
                        $('#edit_allow_installments').prop('checked', true);
                        $('#edit_installment_options').show();
                        $('#edit_default_installment_type').val(fee.default_installment_type || '');
                        $('#edit_number_of_installments').val(fee.number_of_installments || '');
                        $('#edit_allow_partial_payment').prop('checked', fee.allow_partial_payment !== false);
                    } else {
                        $('#edit_allow_installments').prop('checked', false);
                        $('#edit_installment_options').hide();
                    }
                    
                    // Other fees details
                    editOtherFeeDetailCounter = 0;
                    if (fee.fee_type === 'Other Fees') {
                        $('#edit_other_fees_details_section').show();
                        $('#edit_other_fees_details_list').empty();
                        $('#edit_amount_help_text').text('Total will be calculated from Other Fees Details below');
                        
                        if (fee.other_fee_details && fee.other_fee_details.length > 0) {
                            fee.other_fee_details.forEach(function(detail) {
                                editOtherFeeDetailCounter++;
                                const detailHtml = `
                                    <div class="edit-other-fee-detail-item border rounded p-3 mb-3" data-detail-index="${editOtherFeeDetailCounter}">
                                        <div class="row g-3">
                                            <div class="col-md-5">
                                                <label class="form-label small fw-bold">Item Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-sm edit-other-fee-detail-name" value="${detail.fee_detail_name || ''}" placeholder="e.g., Food, Study Tour, Library" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold">Amount (TZS) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control form-control-sm edit-other-fee-detail-amount" value="${detail.amount || 0}" placeholder="0.00" step="0.01" min="0" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small fw-bold">&nbsp;</label>
                                                <button type="button" class="btn btn-sm btn-danger w-100 remove-edit-other-fee-detail" title="Remove">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-bold">Description (Optional)</label>
                                                <textarea class="form-control form-control-sm edit-other-fee-detail-description" rows="2" placeholder="Optional description">${detail.description || ''}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                $('#edit_other_fees_details_list').append(detailHtml);
                            });
                            updateEditOtherFeesTotal();
                        }
                    } else {
                        $('#edit_other_fees_details_section').hide();
                        $('#edit_amount_help_text').text('Total fee amount');
                        $('#edit_other_fees_details_list').empty();
                    }
                    
                    $('#editFeeModal').modal('show');
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to load fee details'
                });
            }
        });
    });

    // Edit Fee Form Submission
    $('#editFeeForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate installment amounts match total fee
        const totalAmount = parseFloat($('#edit_fee_amount').val()) || 0;
        const allowInstallments = $('#edit_allow_installments').is(':checked');
        const numberOfInstallments = parseInt($('#edit_number_of_installments').val()) || 0;
        
        if (allowInstallments && numberOfInstallments > 0) {
            const calculatedTotal = (totalAmount / numberOfInstallments) * numberOfInstallments;
            const difference = Math.abs(calculatedTotal - totalAmount);
            
            if (difference > 0.01) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Installment amounts do not match the total fee amount. Please check your calculations.',
                    confirmButtonColor: '#940000'
                });
                return;
            }
        }
        
        // Collect other fees details if fee type is Other Fees
        let otherFeesDetails = [];
        if ($('#edit_fee_type_select').val() === 'Other Fees') {
            $('.edit-other-fee-detail-item').each(function() {
                const name = $(this).find('.edit-other-fee-detail-name').val();
                const amount = $(this).find('.edit-other-fee-detail-amount').val();
                const description = $(this).find('.edit-other-fee-detail-description').val();
                
                if (name && amount) {
                    otherFeesDetails.push({
                        name: name,
                        amount: parseFloat(amount),
                        description: description || null
                    });
                }
            });
        }
        
        // Ensure checkbox values are properly set
        $('#edit_allow_installments').val($('#edit_allow_installments').is(':checked') ? 1 : 0);
        $('#edit_allow_partial_payment').val($('#edit_allow_partial_payment').is(':checked') ? 1 : 0);
        
        const feeID = $('#edit_fee_id').val();
        let formData = $(this).serialize();
        
        // Add other fees details to form data
        if (otherFeesDetails.length > 0) {
            formData += '&other_fees_details=' + encodeURIComponent(JSON.stringify(otherFeesDetails));
            console.log('Form Data with Other Fees:', formData);
        }
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        const modal = $('#editFeeModal');
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Inaupdate...');
        
        // Show loading overlay
        $('#loadingOverlay').addClass('show');
        
        // Disable all form inputs during submission
        modal.find('input, select, textarea').prop('disabled', true);
        
        console.log('Sending update request to server...');
        
        $.ajax({
            url: `/update_fee/${feeID}`,
            method: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                console.log('Update request sent, waiting for response...');
            },
            success: function(response) {
                console.log('Server response received:', response);
                
                if (response && response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Imefanikiwa!',
                        text: response.message || 'Ada imesasishwa kwa mafanikio',
                        timer: 2500,
                        showConfirmButton: true,
                        confirmButtonColor: '#940000'
                    }).then((result) => {
                        modal.modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tahadhari!',
                        text: response.message || 'Imeupdate lakini hakuna response ya uhakika',
                        confirmButtonColor: '#940000'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr, status, error);
                
                let errorMessage = 'Imeshindwa kusasisha ada. Tafadhali jaribu tena.';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('<br>');
                    }
                } else if (xhr.status === 0) {
                    errorMessage = 'Hakuna muunganisho na server. Tafadhali angalia muunganisho wako.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Kosa la server. Tafadhali wasiliana na msimamizi.';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Kosa!',
                    html: errorMessage,
                    confirmButtonColor: '#940000'
                });
            },
            complete: function() {
                console.log('Update request completed');
                // Hide loading overlay
                $('#loadingOverlay').removeClass('show');
                // Re-enable form inputs
                modal.find('input, select, textarea').prop('disabled', false);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Delete Fee Button Click
    $(document).on('click', '.delete-fee-btn', function() {
        const feeID = $(this).data('fee-id');
        const feeName = $(this).data('fee-name');
        const deleteBtn = $(this);
        
        Swal.fire({
            title: 'Je, una uhakika?',
            text: `Unataka kufuta "${feeName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ndio, futa!',
            cancelButtonText: 'Ghairi'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading on button
                const originalHtml = deleteBtn.html();
                deleteBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                
                console.log('Sending delete request to server...');
                
                $.ajax({
                    url: `/delete_fee/${feeID}`,
                    method: 'DELETE',
                    dataType: 'json',
                    beforeSend: function() {
                        console.log('Delete request sent, waiting for response...');
                    },
                    success: function(response) {
                        console.log('Server response received:', response);
                        
                        if (response && response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Imefutwa!',
                                text: response.message || 'Ada imefutwa kwa mafanikio',
                                timer: 2000,
                                showConfirmButton: true,
                                confirmButtonColor: '#940000'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Tahadhari!',
                                text: response.message || 'Imejaribu kufuta lakini hakuna response ya uhakika',
                                confirmButtonColor: '#940000'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred:', xhr, status, error);
                        
                        let errorMessage = 'Imeshindwa kufuta ada. Tafadhali jaribu tena.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 0) {
                            errorMessage = 'Hakuna muunganisho na server. Tafadhali angalia muunganisho wako.';
                        } else if (xhr.status === 500) {
                            errorMessage = 'Kosa la server. Tafadhali wasiliana na msimamizi.';
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Kosa!',
                            html: errorMessage,
                            confirmButtonColor: '#940000'
                        });
                    },
                    complete: function() {
                        console.log('Delete request completed');
                        deleteBtn.prop('disabled', false).html(originalHtml);
                    }
                });
            }
        });
    });

        // View Installment Button Click - For individual fee
        $(document).on('click', '.view-installment-btn', function() {
            const feeID = $(this).data('fee-id');
            const feeType = $(this).data('fee-type');
            
            $('#viewInstallmentContent').html('<div class="text-center py-5"><div class="spinner-border text-primary-custom" role="status"></div><p class="mt-3">Loading...</p></div>');
            $('#viewInstallmentModal').modal('show');
            
            // Get fee details with installments
            $.ajax({
                url: `/get_fee/${feeID}`,
                method: 'GET',
                success: function(feeResponse) {
                    if (feeResponse.success) {
                        const fee = feeResponse.fee;
                        $('#installmentClassName').text(fee.fee_name);
                        
                        // Use installments from fee response if available
                        let installments = [];
                        if (feeResponse.fee && feeResponse.fee.installments) {
                            installments = feeResponse.fee.installments;
                        } else if (feeResponse.installments) {
                            installments = feeResponse.installments;
                        }
                        
                        // Process installments
                        const response = { success: true, installments: installments };
                        if (response.success) {
                            let html = '';
                            
                            if (response.installments && response.installments.length > 0) {
                                // Sort installments by installment_number
                                response.installments.sort(function(a, b) {
                                    return (a.installment_number || 0) - (b.installment_number || 0);
                                });
                                
                                html += '<div class="table-responsive">';
                                html += '<table class="table table-bordered table-hover mb-0">';
                                html += '<thead style="background-color: #940000; color: white;">';
                                html += '<tr>';
                                html += '<th style="width: 60%; color: white;">Installment</th>';
                                html += '<th style="width: 40%; color: white;" class="text-end">Amount (TZS)</th>';
                                html += '</tr>';
                                html += '</thead>';
                                html += '<tbody>';
                                
                                let total = 0;
                                response.installments.forEach(function(inst) {
                                    const amount = parseFloat(inst.amount || 0);
                                    total += amount;
                                    
                                    html += '<tr>';
                                    html += '<td><strong>' + (inst.installment_name || 'N/A') + '</strong></td>';
                                    html += '<td class="text-end"><strong>' + amount.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</strong></td>';
                                    html += '</tr>';
                                });
                                
                                html += '</tbody>';
                                html += '<tfoot class="table-light">';
                                html += '<tr>';
                                html += '<th class="text-end">Total:</th>';
                                html += '<th class="text-end text-primary">' + total.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '/=</th>';
                                html += '</tr>';
                                html += '</tfoot>';
                                html += '</table>';
                                html += '</div>';
                            } else {
                                html = '<div class="alert alert-warning mb-0">';
                                html += '<i class="bi bi-exclamation-triangle"></i> No installments allowed';
                                html += '</div>';
                            }
                            
                            $('#viewInstallmentContent').html(html);
                        } else {
                            $('#viewInstallmentContent').html('<div class="alert alert-danger">Failed to retrieve installments. ' + (response.message || '') + '</div>');
                        }
                    } else {
                        $('#viewInstallmentContent').html('<div class="alert alert-danger">Failed to retrieve fee details.</div>');
                    }
                },
                error: function(xhr) {
                    $('#viewInstallmentContent').html('<div class="alert alert-danger">Failed to retrieve fee details.</div>');
                }
            });
        });

        // Edit Class Fees Button Click - Opens Add Fee Modal with class pre-selected (for empty classes)
        $(document).on('click', '.edit-class-fees-btn', function() {
            const classID = $(this).data('class-id');
            $('#fee_class_select').val(classID);
            $('#addFeeModal').modal('show');
    });

        // Reset forms when modals are closed
        $('#addFeeModal').on('hidden.bs.modal', function() {
            $('#addFeeForm')[0].reset();
        });
        
        $('#editFeeModal').on('hidden.bs.modal', function() {
            $('#editFeeForm')[0].reset();
        });

        // Export PDF Function
        $(document).on('click', '#exportPdfBtn', function() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4'); // Portrait orientation
            
            // Get school name and logo from blade variables
            const schoolName = '{{ $schoolName }}';
            const schoolLogo = '{{ $schoolLogo }}';
            
            let yPos = 15;
            
            // Function to generate PDF table
            function generatePDFTable(logoLoaded = false) {
                // Add logo if available and loaded (center)
                if (logoLoaded && schoolLogo && schoolLogo !== '') {
                    yPos += 5; // Space before logo
                }
                
                // Add school name (centered)
                doc.setFontSize(16);
                doc.setFont('helvetica', 'bold');
                doc.text(schoolName, 105, yPos, { align: 'center' }); // Center for portrait
                yPos += 10;
                
                // Add title
                doc.setFontSize(14);
                doc.setFont('helvetica', 'bold');
                doc.text('FEES STRUCTURE FOR ALL CLASSES', 105, yPos, { align: 'center' });
                yPos += 15;
                
                // Collect class data with other fee details as vertical text
                const classData = [];
                
                $('table tbody tr').each(function() {
                    const rowData = {
                        className: '',
                        tuitionFee: '',
                        otherFeeDetails: '', // Will be vertical text
                        totalFee: ''
                    };
                    
                    $(this).find('td').each(function(index) {
                        if (index === 0) {
                            // Class column
                            let classText = $(this).clone();
                            classText.find('i').remove();
                            rowData.className = classText.text().trim();
                        } else if (index === 1) {
                            // Tuition Fee column
                            let tuitionText = $(this).clone();
                            tuitionText.find('.btn-group').remove();
                            rowData.tuitionFee = tuitionText.find('strong.text-success').first().text().trim() || '-';
                        } else if (index === 2) {
                            // Other Fee column - collect all details as vertical text
                            let otherText = $(this).clone();
                            otherText.find('.btn-group').remove();
                            
                            let detailsText = [];
                            otherText.find('.text-muted.small').each(function() {
                                const detailName = $(this).text().trim().replace(/^[•·]\s*/, '');
                                const detailAmount = $(this).closest('.mb-1').find('.text-warning.small').text().trim();
                                if (detailName && detailAmount) {
                                    detailsText.push(detailName + ': ' + detailAmount);
                                }
                            });
                            
                            // Get total
                            const total = otherText.find('strong.text-warning').last().text().trim();
                            if (detailsText.length > 0) {
                                detailsText.push('Total: ' + total);
                                rowData.otherFeeDetails = detailsText.join('\n');
                            } else {
                                rowData.otherFeeDetails = total || '-';
                            }
                        } else if (index === 3) {
                            // Total Fee column
                            rowData.totalFee = $(this).text().trim();
                        }
                    });
                    
                    if (rowData.className !== '') {
                        classData.push(rowData);
                    }
                });
                
                // Create headers (simple - no dynamic columns)
                const tableHeaders = ['Class', 'Tuition Fee (TZS)', 'Other Fee (TZS)', 'Total Fee (TZS)'];
                
                // Prepare table data
                const tableData = [];
                classData.forEach(function(row) {
                    tableData.push([row.className, row.tuitionFee, row.otherFeeDetails, row.totalFee]);
                });
                
                // Add footer row
                const footerRow = ['TOTAL'];
                const tfootCells = $('table tfoot tr').first().find('td, th');
                footerRow.push(tfootCells.eq(1).text().trim()); // Tuition Total
                footerRow.push(tfootCells.eq(2).text().trim()); // Other Total
                footerRow.push(tfootCells.eq(3).text().trim()); // Grand Total
                
                if (footerRow.length > 0) {
                    tableData.push(footerRow);
                }
                
                // Add table
                doc.autoTable({
                    head: [tableHeaders],
                    body: tableData,
                    startY: yPos,
                    styles: { 
                        fontSize: 8,
                        cellPadding: 3,
                        lineWidth: 0.1
                    },
                    headStyles: {
                        fillColor: [148, 0, 0],
                        textColor: 255,
                        fontStyle: 'bold',
                        fontSize: 9
                    },
                    columnStyles: {
                        0: { cellWidth: 40 }, // Class
                        1: { cellWidth: 40 }, // Tuition Fee
                        2: { cellWidth: 60, cellPadding: 4 }, // Other Fee (wider for vertical text)
                        3: { cellWidth: 40 }  // Total Fee
                    },
                    alternateRowStyles: {
                        fillColor: [245, 245, 245]
                    },
                    margin: { top: yPos, left: 10, right: 10 }
                });
                
                // Save PDF
                doc.save('Fees_Structure_' + new Date().toISOString().split('T')[0] + '.pdf');
            }
            
            // Load logo if available
            if (schoolLogo && schoolLogo !== '') {
                try {
                    const img = new Image();
                    img.crossOrigin = 'anonymous';
                    img.src = schoolLogo;
                    
                    img.onload = function() {
                        try {
                            doc.addImage(img, 'PNG', 85, yPos, 40, 20); // Centered for portrait (105 - 20 = 85)
                            yPos += 25;
                            generatePDFTable(true);
                        } catch(e) {
                            console.log('Logo add error:', e);
                            yPos = 15;
                            generatePDFTable(false);
                        }
                    };
                    
                    img.onerror = function() {
                        yPos = 15;
                        generatePDFTable(false);
                    };
                } catch(e) {
                    yPos = 15;
                    generatePDFTable(false);
                }
            } else {
                generatePDFTable(false);
            }
        });

        // Export Excel Function
        $(document).on('click', '#exportExcelBtn', function() {
            // Get school name from blade variables
            const schoolName = '{{ $schoolName }}';
            
            // Collect class data with other fee details as vertical text
            const classData = [];
            
            $('table tbody tr').each(function() {
                const rowData = {
                    className: '',
                    tuitionFee: '',
                    otherFeeDetails: '', // Will be vertical text
                    totalFee: ''
                };
                
                $(this).find('td').each(function(index) {
                    if (index === 0) {
                        // Class column
                        let classText = $(this).clone();
                        classText.find('i').remove();
                        rowData.className = classText.text().trim();
                    } else if (index === 1) {
                        // Tuition Fee column
                        let tuitionText = $(this).clone();
                        tuitionText.find('.btn-group').remove();
                        rowData.tuitionFee = tuitionText.find('strong.text-success').first().text().trim() || '-';
                    } else if (index === 2) {
                        // Other Fee column - collect all details as vertical text
                        let otherText = $(this).clone();
                        otherText.find('.btn-group').remove();
                        
                        let detailsText = [];
                        otherText.find('.text-muted.small').each(function() {
                            const detailName = $(this).text().trim().replace(/^[•·]\s*/, '');
                            const detailAmount = $(this).closest('.mb-1').find('.text-warning.small').text().trim();
                            if (detailName && detailAmount) {
                                detailsText.push(detailName + ': ' + detailAmount);
                            }
                        });
                        
                        // Get total
                        const total = otherText.find('strong.text-warning').last().text().trim();
                        if (detailsText.length > 0) {
                            detailsText.push('Total: ' + total);
                            rowData.otherFeeDetails = detailsText.join('\n');
                        } else {
                            rowData.otherFeeDetails = total || '-';
                        }
                    } else if (index === 3) {
                        // Total Fee column
                        rowData.totalFee = $(this).text().trim();
                    }
                });
                
                if (rowData.className !== '') {
                    classData.push(rowData);
                }
            });
            
            // Prepare data with header
            const data = [];
            
            // Add school name (centered)
            data.push([schoolName]);
            data.push(['FEES STRUCTURE FOR ALL CLASSES']);
            data.push(['']); // Empty row
            
            // Create headers (simple - no dynamic columns)
            const tableHeaders = ['Class', 'Tuition Fee (TZS)', 'Other Fee (TZS)', 'Total Fee (TZS)'];
            data.push(tableHeaders);
            
            // Add data rows
            classData.forEach(function(row) {
                data.push([row.className, row.tuitionFee, row.otherFeeDetails, row.totalFee]);
            });
            
            // Add footer row
            const footerRow = ['TOTAL'];
            const tfootCells = $('table tfoot tr').first().find('td, th');
            footerRow.push(tfootCells.eq(1).text().trim()); // Tuition Total
            footerRow.push(tfootCells.eq(2).text().trim()); // Other Total
            footerRow.push(tfootCells.eq(3).text().trim()); // Grand Total
            data.push(footerRow);
            
            // Create workbook
            const wb = XLSX.utils.book_new();
            
            // Create worksheet
            const ws = XLSX.utils.aoa_to_sheet(data);
            
            // Set column widths
            ws['!cols'] = [
                { wch: 15 }, // Class
                { wch: 20 }, // Tuition Fee
                { wch: 30 }, // Other Fee (wider for vertical text)
                { wch: 18 }  // Total Fee
            ];
            
            // Merge cells for header
            if (!ws['!merges']) ws['!merges'] = [];
            ws['!merges'].push({ s: { r: 0, c: 0 }, e: { r: 0, c: tableHeaders.length - 1 } }); // School name
            ws['!merges'].push({ s: { r: 1, c: 0 }, e: { r: 1, c: tableHeaders.length - 1 } }); // Title
            
            // Style header cells
            if (ws['A1']) {
                ws['A1'].s = { font: { bold: true, sz: 16 }, alignment: { horizontal: 'center' } };
            }
            if (ws['A2']) {
                ws['A2'].s = { font: { bold: true, sz: 14 }, alignment: { horizontal: 'center' } };
            }
            
            // Add worksheet to workbook
            XLSX.utils.book_append_sheet(wb, ws, 'Fees Structure');
            
            // Save file
            XLSX.writeFile(wb, 'Fees_Structure_' + new Date().toISOString().split('T')[0] + '.xlsx');
        });
    }

    // Initialize when DOM and jQuery are ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            // Wait a bit for jQuery to load from footer
            setTimeout(initFeesManagement, 200);
        });
    } else {
        // DOM is already ready, wait for jQuery
        setTimeout(initFeesManagement, 200);
    }
})();
</script>
