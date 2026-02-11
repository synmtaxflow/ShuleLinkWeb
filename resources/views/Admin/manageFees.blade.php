@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
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
                            <table id="feesStructureTable" class="table table-bordered table-hover mb-0" style="border-collapse: separate; border-spacing: 0;">
                                <thead style="background-color: #940000 !important;">
                                    <tr style="background-color: #940000 !important;">
                                        <th style="width: 12%; background-color: #940000 !important;" class="text-center border-end text-white">Class</th>
                                        <th style="width: 25%; background-color: #940000 !important;" class="border-end text-white">Fee Name</th>
                                        <th style="width: 15%; background-color: #940000 !important;" class="text-end border-end text-white">Amount (TZS)</th>
                                        <th style="width: 15%; background-color: #940000 !important;" class="border-end text-white">Deadline</th>
                                        <th style="width: 13%; background-color: #940000 !important;" class="text-center border-end text-white">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                @foreach($classes as $class)
                    @php
                        $classFees = $feesByClass->get($class->classID, collect())->where('status', 'Active');
                        $feeCount = $classFees->count();
                        $rowSpan = $feeCount > 0 ? $feeCount : 1;
                    @endphp
                    
                    @if($feeCount > 0)
                        @foreach($classFees as $index => $fee)
                            <tr class="align-middle">
                                @if($index === 0)
                                    <!-- Class Column (Rowspan) -->
                                    <td class="text-center border-end align-middle bg-light" rowspan="{{ $rowSpan }}">
                                        <strong class="text-dark" style="font-size: 1.1rem;">
                                            <i class="bi bi-mortarboard text-primary-custom"></i> {{ $class->class_name }}
                                        </strong>
                                        <div class="mt-2 pt-2 border-top">
                                            <small class="text-muted">Total: </small><br>
                                            <strong class="text-primary-custom">{{ number_format($classFees->sum('amount'), 0) }}/=</strong>
                                        </div>
                                    </td>
                                @endif

                                <!-- Fee Name -->
                                <td class="border-end">
                                    <span class="fw-bold">{{ $fee->fee_name }}</span>
                                    @if($fee->description)
                                        <br><small class="text-muted">{{ $fee->description }}</small>
                                    @endif
                                </td>

                                <!-- Amount -->
                                <td class="text-end border-end">
                                    <strong class="text-dark">{{ number_format($fee->amount, 0) }}/=</strong>
                                </td>



                                <!-- Deadline -->
                                <td class="border-end">
                                    @if($fee->payment_deadline_amount || $fee->payment_deadline_date)
                                        @if($fee->payment_deadline_amount)
                                            <small class="text-danger fw-bold">Amount: {{ number_format($fee->payment_deadline_amount, 0) }}/=</small><br>
                                        @endif
                                        @if($fee->payment_deadline_date)
                                            <small class="text-muted">Hadi: {{ $fee->payment_deadline_date->format('d/m/Y') }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="text-center border-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-sm btn-outline-info view-installment-btn" 
                                                data-fee-id="{{ $fee->feeID }}"
                                                title="View Installments">
                                            <i class="bi bi-calendar-range"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary edit-fee-btn" 
                                                data-fee-id="{{ $fee->feeID }}"
                                                title="Edit Fee">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-fee-btn" 
                                                data-fee-id="{{ $fee->feeID }}"
                                                data-fee-name="{{ $fee->fee_name }}"
                                                title="Delete Fee">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <!-- Class with no fees -->
                        <tr class="align-middle">
                            <td class="text-center border-end align-middle bg-light">
                                <strong class="text-dark" style="font-size: 1.1rem;">
                                    <i class="bi bi-mortarboard text-primary-custom"></i> {{ $class->class_name }}
                                </strong>
                            </td>
                            <td colspan="4" class="text-center text-muted py-3">
                                <em>No fees assigned to this class</em>
                            </td>
                        </tr>
                    @endif
                @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td class="text-center fw-bold border-end" colspan="2">
                                            <strong>TOTAL FEES</strong>
                                        </td>
                                        <td class="text-end border-end" colspan="3">
                                            <strong class="text-primary-custom" style="font-size: 1.5rem;">
                                                {{ number_format($classes->sum(function($class) use ($feesByClass) {
                                                    return $feesByClass->get($class->classID, collect())->where('status', 'Active')->sum('amount');
                                                }), 0) }}/=
                                            </strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                </div>
            @endif

            <!-- SMS Templates Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0 small"><i class="bi bi-chat-left-text"></i> SMS Templates</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="small fw-bold">Ada Imetajwa (Fee Assigned)</h6>
                            <div class="p-2 bg-light rounded border">
                                <small class="text-muted">Habari [Mzazi], mwanafunzi [Jina] amepangiwa ada ya [Ada] TZS [Kiasi] kwa mwaka [Mwaka]. Tafadhali lipa kupitia Control Number: [Namba].</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="small fw-bold">Kumbusho la Deni (Debt Reminder)</h6>
                            <div class="p-2 bg-light rounded border">
                                <small class="text-muted">Ndugu [Mzazi], mwanafunzi [Jina] ana deni la ada kiasi cha TZS [Kiasi]. Tafadhali kamilisha malipo kabla ya tarehe [Tarehe] ili kuepuka usumbufu.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

            <!-- Add Fee Modal -->
<div class="modal fade" id="addFeeModal" tabindex="-1" aria-labelledby="addFeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addFeeModalLabel">
                    <i class="bi bi-plus-circle"></i> Add Fee to Class
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addFeeForm">
                @csrf
                <div class="modal-body">
                    <!-- Select Class -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Class <span class="text-danger">*</span></label>
                        <select name="classID" id="fee_class_select" class="form-select" required>
                            <option value="">Select class...</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <hr>
                    
                    <!-- Fees List Container -->
                    <div id="fees_list_container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><i class="bi bi-list-ul"></i> Fees</h6>
                            <button type="button" class="btn btn-sm btn-primary-custom" id="add_fee_row_btn">
                                <i class="bi bi-plus-circle"></i> Add Fee
                            </button>
                        </div>
                        
                        <!-- Fees will be added here dynamically -->
                        <div id="fees_rows">
                            <!-- Initial fee row will be added by JavaScript -->
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Total Display -->
                    <div class="card border-primary-custom mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Total Fees:</strong>
                                <strong class="text-primary-custom" style="font-size: 1.3rem;" id="total_fees_display">TZS 0.00</strong>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Duration Field -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Duration <span class="text-danger">*</span></label>
                        <select name="duration" id="fee_duration_select" class="form-select" required>
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
                                <i class="bi bi-calendar-range"></i> Installment Options
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
                                <small class="text-muted">Parents can pay in partial amounts</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allow_installments" id="allow_installments" value="1">
                                    <label class="form-check-label fw-bold" for="allow_installments">
                                        Allow Installments
                                    </label>
                                </div>
                                <small class="text-muted">Parents can pay in installments (monthly, termly, etc.)</small>
                            </div>
                            
                            <div id="installment_options" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Installment Type <span class="text-danger">*</span></label>
                                    <select name="default_installment_type" id="default_installment_type" class="form-select">
                                        <option value="">Select type...</option>
                                        <option value="Semester">Semester</option>
                                        <option value="Month">Month</option>
                                        <option value="Two Months">2 Months</option>
                                        <option value="Term">Term</option>
                                        <option value="Quarter">Quarter</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Number of Installments <span class="text-danger">*</span></label>
                                    <input type="number" name="number_of_installments" id="number_of_installments" class="form-control" min="1" max="12" placeholder="e.g.: 2, 12">
                                    <small class="text-muted">Total installments (e.g. 2 for 2 semesters, 12 for 12 months)</small>
                                </div>
                                <div id="installment_amounts_container" class="mt-3"></div>
                                <div id="installment_total_validation" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-save"></i> Save Fee
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
                        <label class="form-label fw-bold">Fee Name <span class="text-danger">*</span></label>
                        <input type="text" name="fee_name" id="edit_fee_name" class="form-control" placeholder="e.g. Tuition Fee, Bus Fee" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="must_start_pay" id="edit_must_start_pay" value="1">
                                <label class="form-check-label fw-bold" for="edit_must_start_pay">
                                    Must Pay to Start?
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Amount (TZS) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="edit_fee_amount" class="form-control" placeholder="0.00" step="0.01" min="0" required>
                        </div>
                    </div>

                    <div class="card border-danger mb-3">
                        <div class="card-header bg-danger text-white py-2">
                           <h6 class="mb-0 small"><i class="bi bi-alarm"></i> Deadline Info (Optional)</h6>
                        </div>
                        <div class="card-body py-2">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold">Deadline Amount</label>
                                    <input type="number" name="payment_deadline_amount" id="edit_deadline_amount" class="form-control form-control-sm" placeholder="0.00">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold">Deadline Date</label>
                                    <input type="date" name="payment_deadline_date" id="edit_deadline_date" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
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
        <p class="mt-3 mb-0 fw-bold text-primary-custom">Saving...</p>
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
                alert('Modal not found. Please check console for more details.');
                return;
            }
            
            // Try to show modal
            try {
                $('#addFeeModal').modal('show');
                console.log('Modal show command executed');
            } catch (error) {
                console.error('Error showing modal:', error);
                alert('Error showing modal: ' + error.message);
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

        // ==================== NEW FEE STRUCTURE LOGIC ====================
        
        let feeRowCounter = 0;
        
        // Fee Row Template
        function createFeeRow() {
            feeRowCounter++;
            return `
                <div class="fee-row card border-primary-custom mb-3" data-fee-index="${feeRowCounter}">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <strong><i class="bi bi-receipt"></i> Fee #${feeRowCounter}</strong>
                        <button type="button" class="btn btn-sm btn-danger remove-fee-row" ${feeRowCounter === 1 ? 'style="display:none;"' : ''}>
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Fee Name -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Fee Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control fee-name" name="fees[${feeRowCounter}][name]" placeholder="e.g. Tuition, Transport" required>
                                <small class="text-muted">Short name for this fee</small>
                            </div>
                            
                            <!-- Amount -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Amount (TZS) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control fee-amount" name="fees[${feeRowCounter}][amount]" placeholder="0.00" step="0.01" min="0" required>
                                <small class="text-muted">Fee amount</small>
                            </div>
                            
                            <!-- Description -->
                            <div class="col-12">
                                <label class="form-label fw-bold small">Description</label>
                                <textarea class="form-control fee-description" name="fees[${feeRowCounter}][description]" rows="2" placeholder="Fee description (optional)"></textarea>
                            </div>
                            
                            <!-- Must Start Pay Toggle -->
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input must-start-pay-toggle" type="checkbox" name="fees[${feeRowCounter}][must_start_pay]" id="must_pay_${feeRowCounter}" value="1">
                                    <label class="form-check-label fw-bold" for="must_pay_${feeRowCounter}">
                                        <i class="bi bi-exclamation-triangle text-warning"></i> Must Pay to Start
                                    </label>
                                </div>
                                <small class="text-muted">Student cannot start school until this fee is paid</small>
                            </div>
                            
                            <!-- Payment Deadline (Optional) -->
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input deadline-toggle" type="checkbox" id="deadline_toggle_${feeRowCounter}">
                                    <label class="form-check-label fw-bold" for="deadline_toggle_${feeRowCounter}">
                                        Set Deadline
                                    </label>
                                </div>
                                <small class="text-muted">Parents must pay a certain amount by a date</small>
                            </div>
                            
                            <!-- Deadline Details (Hidden by default) -->
                            <div class="col-12 deadline-details" style="display: none;">
                                <div class="card border-warning">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold small">Required Amount (TZS)</label>
                                                <input type="number" class="form-control fee-deadline-amount" name="fees[${feeRowCounter}][deadline_amount]" placeholder="0.00" step="0.01" min="0">
                                                <small class="text-muted">Amount required by deadline</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold small">Deadline Date</label>
                                                <input type="date" class="form-control fee-deadline-date" name="fees[${feeRowCounter}][deadline_date]">
                                                <small class="text-muted">Date to pay the amount</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Initialize with one fee row when modal opens
        $('#addFeeModal').on('shown.bs.modal', function() {
            if ($('#fees_rows').children().length === 0) {
                $('#fees_rows').html(createFeeRow());
                calculateTotalFees();
            }
        });
        
        // Reset form when modal closes
        $('#addFeeModal').on('hidden.bs.modal', function() {
            feeRowCounter = 0;
            $('#fees_rows').empty();
            $('#addFeeForm')[0].reset();
            $('#total_fees_display').text('TZS 0.00');
        });
        
        // Add Fee Row
        $(document).on('click', '#add_fee_row_btn', function() {
            $('#fees_rows').append(createFeeRow());
            updateDeleteButtons();
            calculateTotalFees();
        });
        
        // Remove Fee Row
        $(document).on('click', '.remove-fee-row', function() {
            $(this).closest('.fee-row').remove();
            updateDeleteButtons();
            calculateTotalFees();
        });
        
        // Update delete buttons visibility (hide if only one row)
        function updateDeleteButtons() {
            const rowCount = $('.fee-row').length;
            if (rowCount <= 1) {
                $('.remove-fee-row').hide();
            } else {
                $('.remove-fee-row').show();
            }
        }
        
        // Toggle Deadline Details
        $(document).on('change', '.deadline-toggle', function() {
            const row = $(this).closest('.fee-row');
            const deadlineDetails = row.find('.deadline-details');
            
            if ($(this).is(':checked')) {
                deadlineDetails.slideDown();
            } else {
                deadlineDetails.slideUp();
                // Clear deadline fields
                row.find('.fee-deadline-amount').val('');
                row.find('.fee-deadline-date').val('');
            }
        });
        
        // Calculate Total Fees
        function calculateTotalFees() {
            let total = 0;
            $('.fee-amount').each(function() {
                const amount = parseFloat($(this).val()) || 0;
                total += amount;
            });
            
            $('#total_fees_display').text('TZS ' + total.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            
            // Recalculate installments if enabled
            if ($('#allow_installments').is(':checked')) {
                calculateInstallments();
            }
        }
        
        // Update total when fee amounts change
        $(document).on('input', '.fee-amount', function() {
            calculateTotalFees();
        });
        
        // Update calculateInstallments to use total from all fees
        const originalCalculateInstallments = calculateInstallments;
        function calculateInstallments() {
            // Override amount to use total from all fees
            let totalAmount = 0;
            $('.fee-amount').each(function() {
                totalAmount += parseFloat($(this).val()) || 0;
            });
            
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
                        case 'Two Months': installmentName = '2 Months (' + i + ')'; break;
                        case 'Term': installmentName = 'Term ' + i; break;
                        case 'Quarter': installmentName = 'Quarter ' + i; break;
                        default: installmentName = 'Installment ' + i;
                    }
                    
                    let installmentAmount = baseAmount;
                    if (i === numberOfInstallments) {
                        installmentAmount = totalAmount - totalCalculated;
                    } else {
                        installmentAmount = Math.floor(baseAmount * 100) / 100;
                    }
                    
                    totalCalculated += installmentAmount;
                    html += `<tr><td><strong>${installmentName}</strong></td><td>TZS ${installmentAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td></tr>`;
                }
                
                html += '</tbody></table></div>';
                html += `<div class="mt-2"><strong>Total: TZS ${totalCalculated.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></div>`;
                
                container.html(html);
                
                const difference = Math.abs(totalCalculated - totalAmount);
                const validationDiv = $('#installment_total_validation');
                
                if (difference < 0.01) {
                    validationDiv.html('<div class="alert alert-success mb-0 py-2"><i class="bi bi-check-circle"></i> Installment total matches fee total</div>');
                } else {
                    validationDiv.html('<div class="alert alert-danger mb-0 py-2"><i class="bi bi-exclamation-triangle"></i> Warning: Installment total does not match fee total</div>');
                }
            } else {
                $('#installment_amounts_container').html('<small class="text-muted">Installments will be calculated automatically based on fee total and number of installments.</small>');
                $('#installment_total_validation').html('');
            }
        }
        
        // ==================== END NEW FEE STRUCTURE LOGIC ====================


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

        // Ensure checkbox values are properly set
        $('#allow_installments').val($('#allow_installments').is(':checked') ? 1 : 0);
        $('#allow_partial_payment').val($('#allow_partial_payment').is(':checked') ? 1 : 0);
        
        // Collect checkboxes status as numeric values
        const allowInstallmentsCheckbox = $('#allow_installments').is(':checked') ? 1 : 0;
        const allowPartialCheckbox = $('#allow_partial_payment').is(':checked') ? 1 : 0;
        
        let formData = $(this).serializeArray();
        
        // Ensure checkboxes are present in form data even if unchecked
        formData.push({name: 'allow_installments', value: allowInstallmentsCheckbox});
        formData.push({name: 'allow_partial_payment', value: allowPartialCheckbox});
        
        console.log('Form Data to be sent:', formData);
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        const modal = $('#addFeeModal');
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...');
        
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
                        title: 'Success!',
                        text: response.message || 'Fee assigned successfully',
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
                        title: 'Warning!',
                        text: response.message || 'Saved but uncertain response',
                        confirmButtonColor: '#940000'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr, status, error);
                
                let errorMessage = 'Failed to assign fee. Please try again.';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        // Handle validation errors
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('<br>');
                    }
                } else if (xhr.status === 0) {
                    errorMessage = 'No connection to server. Please check your connection.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Please contact administrator.';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
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
                    $('#edit_fee_name').val(fee.fee_name);
                    $('#edit_must_start_pay').prop('checked', fee.must_start_pay == 1);
                    $('#edit_fee_amount').val(fee.amount);
                    $('#edit_deadline_amount').val(fee.payment_deadline_amount || '');
                    $('#edit_deadline_date').val(fee.payment_deadline_date ? fee.payment_deadline_date.split('T')[0] : '');
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

    // Open Assign Fee Modal
    window.openAssignFeeModal = function() {
        $('#addFeeForm')[0].reset();
        $('#fee_class_select').val(''); // Reset class selection
        $('#addFeeModal').modal('show');
    };

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
        
        // Collect checkboxes status as numeric values
        const allowInstallmentsCheckbox = $('#edit_allow_installments').is(':checked') ? 1 : 0;
        const allowPartialCheckbox = $('#edit_allow_partial_payment').is(':checked') ? 1 : 0;
        const mustStartPayCheckbox = $('#edit_must_start_pay').is(':checked') ? 1 : 0;
        
        let formData = $(this).serializeArray();
        
        // Ensure checkboxes are present in form data even if unchecked
        formData.push({name: 'allow_installments', value: allowInstallmentsCheckbox});
        formData.push({name: 'allow_partial_payment', value: allowPartialCheckbox});
        formData.push({name: 'must_start_pay', value: mustStartPayCheckbox});

        const feeID = $('#edit_fee_id').val();
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        const modal = $('#editFeeModal');
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Updating...');
        
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
                        title: 'Success!',
                        text: response.message || 'Fee updated successfully',
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
                        title: 'Warning!',
                        text: response.message || 'Updated but received no definitive response',
                        confirmButtonColor: '#940000'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr, status, error);
                
                let errorMessage = 'Failed to update fee. Please try again.';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('<br>');
                    }
                } else if (xhr.status === 0) {
                    errorMessage = 'No server connection. Please check your internet.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Please contact administrator.';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
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
            title: 'Are you sure?',
            text: `Do you want to delete "${feeName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete!',
            cancelButtonText: 'Cancel'
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
                                title: 'Deleted!',
                                text: response.message || 'Fee deleted successfully',
                                timer: 2000,
                                showConfirmButton: true,
                                confirmButtonColor: '#940000'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning!',
                                text: response.message || 'Attempted to delete but received no definitive response',
                                confirmButtonColor: '#940000'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred:', xhr, status, error);
                        
                        let errorMessage = 'Failed to delete fee. Please try again.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 0) {
                            errorMessage = 'No server connection. Please check your internet.';
                        } else if (xhr.status === 500) {
                            errorMessage = 'Server error. Please contact administrator.';
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
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
                
                // Clone the table to manipulate it without affecting the view
                const $tempTable = $('#feesStructureTable').clone();
                $tempTable.attr('id', 'tempPdfTable');
                
                // Position off-screen but visible to DOM for autoTable to parse correctly
                $tempTable.css({
                    position: 'absolute',
                    top: '-9999px',
                    left: '-9999px',
                    width: '1000px' // Ensure enough width
                });
                
                // Append to body
                $('body').append($tempTable);
                
                // Remove the "Action" column (last column)
                // Remove header
                $tempTable.find('thead tr th:last-child').remove();
                
                // Remove body/footer cells - handle colspans
                $tempTable.find('tbody tr, tfoot tr').each(function() {
                    const $lastCell = $(this).find('td:last-child');
                    const colspan = parseInt($lastCell.attr('colspan')) || 1;
                    
                    if (colspan > 1) {
                        $lastCell.attr('colspan', colspan - 1);
                    } else {
                        $lastCell.remove();
                    }
                });
                
                // Format Class Column (First column with rowspan)
                $tempTable.find('tbody tr').each(function() {
                     const $classCell = $(this).find('td[rowspan]');
                     if ($classCell.length > 0) {
                         // Extract text
                         let fullText = $classCell.text().replace(/\s+/g, ' ').trim(); // Clean whitespace
                         
                         // Try to find the specific total element for better accuracy
                         let totalAmount = $classCell.find('.text-primary-custom').text().trim();
                         
                         // Get class name (remove total amount from text if possible)
                         // Simple split might be risky if "Total:" is not exact. 
                         // Strategy: Get text of ALL child nodes that are NOT the total container.
                         // But the text is mixed. 
                         // Let's rely on finding 'Total:' substring.
                         let className = fullText.split('Total:')[0].trim();
                         
                         // Update cell content
                         $classCell.text(className + '\n(Total: ' + totalAmount + ')');
                         $classCell.css('vertical-align', 'middle'); // Ensure vertical centering
                     }
                     
                     // Optional: Clean up Fee Name column if needed
                     // const $feeNameCell = $(this).find('td').eq($classCell.length > 0 ? 1 : 0);
                });

                // Generate PDF using the temporary table
                doc.autoTable({
                    html: '#tempPdfTable',
                    startY: yPos,
                    styles: { 
                        fontSize: 9,
                        cellPadding: 3,
                        lineWidth: 0.1,
                        valign: 'middle',
                        halign: 'left'
                    },
                    headStyles: {
                        fillColor: [148, 0, 0],
                        textColor: 255,
                        fontStyle: 'bold',
                        fontSize: 10,
                        halign: 'center'
                    },
                    columnStyles: {
                        0: { cellWidth: 40, halign: 'center', fontStyle: 'bold' }, // Class 
                        1: { cellWidth: 70 }, // Fee Name
                        2: { cellWidth: 35, halign: 'right' }, // Amount
                        3: { cellWidth: 45 }  // Deadline
                    },
                    alternateRowStyles: {
                        fillColor: [250, 250, 250]
                    },
                    margin: { top: yPos, left: 10, right: 10 },
                    didDrawPage: function (data) {
                        // Reseting top margin. The content is already drawn. 
                    }
                });
                
                // Save PDF
                doc.save('Fees_Structure_' + new Date().toISOString().split('T')[0] + '.pdf');
                
                // Cleanup temp table
                $('#tempPdfTable').remove();
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
            
            // Collect fee data directly from table
            const feesData = [];
            
            let currentClassName = '';
            
            $('table tbody tr').each(function() {
                // Check for "No fees"
                if ($(this).text().includes('No fees assigned')) return;
                
                let row = {
                    className: '',
                    feeName: '',
                    amount: '',
                    deadline: ''
                };
                
                let hasClassColumn = $(this).find('td[rowspan]').length > 0;
                
                if (hasClassColumn) {
                    let classCell = $(this).find('td[rowspan]').clone();
                    classCell.find('div.mt-2').remove(); // Remove total block
                    currentClassName = classCell.text().trim();
                }
                
                row.className = currentClassName;
                
                // Fee Name
                let feeNameCell;
                if (hasClassColumn) {
                    feeNameCell = $(this).find('td').eq(1);
                } else {
                    feeNameCell = $(this).find('td').eq(0);
                }
                let feeName = feeNameCell.find('span.fw-bold').text().trim();
                let feeDesc = feeNameCell.find('small.text-muted').text().trim();
                row.feeName = feeName + (feeDesc ? ' (' + feeDesc + ')' : '');
                
                // Amount
                let amountCell;
                if (hasClassColumn) {
                    amountCell = $(this).find('td').eq(2);
                } else {
                    amountCell = $(this).find('td').eq(1);
                }
                row.amount = amountCell.text().trim();
                
                // Deadline
                let deadlineCell;
                if (hasClassColumn) {
                    deadlineCell = $(this).find('td').eq(3);
                } else {
                    deadlineCell = $(this).find('td').eq(2);
                }
                row.deadline = deadlineCell.text().trim();
                
                feesData.push(row);
            });
            
            // Prepare data with header
            const data = [];
            
            // Add school name (centered)
            data.push([schoolName]);
            data.push(['FEES STRUCTURE FOR ALL CLASSES']);
            data.push(['']); // Empty row
            
            // Create headers
            const tableHeaders = ['Class', 'Fee Name', 'Amount', 'Deadline'];
            data.push(tableHeaders);
            
            // Add data rows
            feesData.forEach(function(row) {
                data.push([row.className, row.feeName, row.amount, row.deadline]);
            });
            
            // Add footer row
            const totalText = $('table tfoot strong.text-primary-custom').text().trim();
            data.push(['', '', 'TOTAL', totalText]);
            
            // Create workbook
            const wb = XLSX.utils.book_new();
            
            // Create worksheet
            const ws = XLSX.utils.aoa_to_sheet(data);
            
            // Set column widths
            ws['!cols'] = [
                { wch: 15 }, // Class
                { wch: 40 }, // Fee Name
                { wch: 20 }, // Amount
                { wch: 20 }  // Deadline
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
