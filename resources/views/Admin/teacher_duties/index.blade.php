@extends('layouts.vali')

@push('styles')
<style>
    .week-card {
        transition: transform 0.2s;
    }
    .week-card:hover {
        border-color: #007bff !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    @keyframes pulse-indicator {
        0% { transform: scale(0.95); opacity: 0.8; }
        50% { transform: scale(1.1); opacity: 1; }
        100% { transform: scale(0.95); opacity: 0.8; }
    }
    .active-indicator {
        display: inline-block;
        background: #28a745;
        color: white;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: bold;
        margin-bottom: 5px;
        animation: pulse-indicator 2s infinite;
        text-transform: uppercase;
        box-shadow: 0 0 10px rgba(40, 167, 69, 0.4);
    }
    .active-week {
        border-left: 5px solid #28a745 !important;
        background-color: rgba(40, 167, 69, 0.05) !important;
    }
    .filter-card {
        background: #f8f9fa;
        border: 1px solid #e3e6f0;
        border-radius: 10px;
    }
    .modal-xl { max-width: 95%; }
    .transition-icon { transition: transform 0.3s ease; }
    .expandable-row[aria-expanded="true"] .transition-icon { transform: rotate(90deg); }
    .expandable-row:hover { background-color: rgba(0, 123, 255, 0.05) !important; }
    #attendance_table input { width: 45px; text-align: center; border: 1px solid #eee; padding: 2px; }
    .btn-xs { padding: 0.1rem 0.3rem; font-size: 0.75rem; }
</style>
@endpush

@section('content')

<div class="breadcrumbs">
    <div class="col-sm-6">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Teacher on Duties in <u>{{ $monthTitle }}</u></h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="#">Duties Book</a></li>
                    <li class="active">Teacher on Duties</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div id="alertPlaceholder"></div>

                <!-- Filters -->
                <div class="card filter-card mb-4">
                    <div class="card-body">
                        <form id="filterForm" class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-control-label">From Date</label>
                                <input type="date" name="from_date" id="filter_from" class="form-control" value="{{ $fromDate }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-control-label">To Date</label>
                                <input type="date" name="to_date" id="filter_to" class="form-control" value="{{ $toDate }}">
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="submit" class="btn btn-info">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                                <button type="button" id="resetFilters" class="btn btn-outline-secondary">
                                    <i class="fa fa-refresh"></i> Reset
                                </button>
                                <button type="button" id="downloadPdf" class="btn btn-danger">
                                    <i class="fa fa-file-pdf-o"></i> Download PDF
                                </button>
                                <button type="button" class="btn btn-primary" id="openAssignModal">
                                    <i class="fa fa-plus"></i> Assign Teacher Duties
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">Duties Roster - {{ $monthTitle }}</strong>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Week</th>
                                    <th>Teacher(s)</th>
                                    <th>Dates</th>
                                    <th>Term</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="dutiesTableBody">
                                @include('Admin.teacher_duties.table_body')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Duty Modal -->
<div class="modal fade" id="assignDutyModal" tabindex="-1" role="dialog" aria-labelledby="assignDutyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignDutyModalLabel">Assign Teacher Duties Time Table</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="dutyForm">
                @csrf
                <div class="modal-body">
                    
                    @if($lastDutyEndDate)
                    <div class="card bg-light mb-3" id="continuityPrompt">
                        <div class="card-body">
                            <h6 class="card-title">Continuity Check</h6>
                            <p class="mb-2">Your last duty roster ended on <strong>{{ \Carbon\Carbon::parse($lastDutyEndDate)->format('d M Y') }}</strong>.</p>
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-outline-primary active" id="btnContinue">
                                    <input type="radio" name="options" id="optContinue" autocomplete="off" checked> Continue Assignment
                                </label>
                                <label class="btn btn-outline-secondary" id="btnNewDate">
                                    <input type="radio" name="options" id="optNewDate" autocomplete="off"> Pick New Start Date
                                </label>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="form-group" id="startDateGroup">
                        <label for="start_date" class="form-control-label">Starting Date of Time Table <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="start_date" class="form-control" required @if($lastDutyEndDate) readonly value="{{ \Carbon\Carbon::parse($lastDutyEndDate)->addDay()->format('Y-m-d') }}" @endif>
                        <small class="form-text text-muted">Holidays will be automatically skipped based on school calendar.</small>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Weekly Schedule Assignment</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-week-btn">
                            <i class="fa fa-plus"></i> Add Next Week
                        </button>
                    </div>

                    <div id="weeks-container">
                        <!-- Dynamic week rows will be appended here -->
                        <div class="text-center text-muted py-5" id="no-weeks-msg">Click "Add Next Week" to begin assigning teachers.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveRoster">Save Roster & Send SMS</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template for Teachers Select (Hidden) -->
<div id="teacher-select-template" style="display:none;">
    <div class="teacher-row d-flex mb-2 align-items-center">
        <div style="flex-grow: 1;">
            <select class="form-control" required>
                <option value="">Select Teacher...</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->first_name }} {{ $teacher->last_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="ml-2">
            <button type="button" class="btn btn-outline-danger btn-sm remove-teacher-btn" title="Remove this teacher"><i class="fa fa-times"></i></button>
        </div>
    </div>
</div>

@include('partials.duty_report_modal', ['classes' => $classes])
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script>
    jQuery(document).ready(function($) {
        let signaturePad;
        const canvas = document.getElementById('signature-pad');

        if (canvas && typeof SignaturePad !== 'undefined') {
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 128)'
            });

            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                signaturePad.clear();
            }

            window.onresize = resizeCanvas;
            $('#dutyReportModal').on('shown.bs.modal', function() {
                resizeCanvas();
            });

            $('#clear-signature').click(function() {
                signaturePad.clear();
            });
        }
        
        let lastEndDate = "{{ $lastDutyEndDate }}";


        $('#btnContinue').click(function() {
            $('#start_date').prop('readonly', true).val(moment(lastEndDate).add(1, 'days').format('YYYY-MM-DD'));
            updateAllWeekIndices();
        });

        $('#btnNewDate').click(function() {
            $('#start_date').prop('readonly', false);
            updateAllWeekIndices();
        });

        $('#start_date').on('change', function() {
            updateAllWeekIndices();
        });

        $('#add-week-btn').click(function() {
            addWeekRow();
        });

        function addWeekRow() {
            let startDate = $('#start_date').val();
            if (!startDate) {
                alert("Please select a starting date first.");
                return;
            }

            $('#no-weeks-msg').hide();

            let rowHtml = `
                <div class="card week-card mb-3" style="border: 1px solid #e0e0e0; background-color: #fafafa;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong class="week-title">Week</strong>
                                <span class="badge badge-secondary ml-2 week-dates">Dates</span>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-week-btn" title="Remove Week"><i class="fa fa-trash"></i></button>
                        </div>
                        
                        <div class="week-teachers-list">
                            <!-- Teachers inputs go here -->
                        </div>

                        <button type="button" class="btn btn-sm btn-link pl-0 add-teacher-btn">
                            <i class="fa fa-user-plus"></i> Add Another Teacher
                        </button>
                    </div>
                </div>
            `;

            let $newRow = $(rowHtml);
            $('#weeks-container').append($newRow);
            
            addTeacherInput($newRow);
            updateAllWeekIndices();
        }

        $(document).on('click', '.add-teacher-btn', function() {
            let $weekCard = $(this).closest('.week-card');
            addTeacherInput($weekCard);
            let index = $('#weeks-container .week-card').index($weekCard);
            $weekCard.find('.week-teachers-list .teacher-row:last select').attr('name', 'weeks[' + index + '][teachers][]');
        });

        function addTeacherInput($weekCard) {
            let template = $('#teacher-select-template').html();
            let $teacherList = $weekCard.find('.week-teachers-list');
            $teacherList.append(template);
        }

        $(document).on('click', '.remove-teacher-btn', function() {
            let $list = $(this).closest('.week-teachers-list');
            if ($list.find('.teacher-row').length > 1) {
                $(this).closest('.teacher-row').remove();
            } else {
                alert("A week must have at least one teacher.");
            }
        });

        $(document).on('click', '.remove-week-btn', function() {
            $(this).closest('.week-card').remove();
            if ($('#weeks-container .week-card').length === 0) {
                $('#no-weeks-msg').show();
            }
            updateAllWeekIndices();
        });

        function updateAllWeekIndices() {
            let startDateVal = $('#start_date').val();
            if (!startDateVal) return;
            
            $('#weeks-container .week-card').each(function(index) {
                let startOfThisWeek = moment(startDateVal).add(index, 'weeks');
                let endOfThisWeek = moment(startOfThisWeek).add(6, 'days');
                let dateRangeStr = startOfThisWeek.format('DD/MM/YYYY') + ' - ' + endOfThisWeek.format('DD/MM/YYYY');
                let weekNum = index + 1;

                $(this).find('.week-title').text('Week ' + weekNum);
                $(this).find('.week-dates').text(dateRangeStr);
                $(this).find('select').attr('name', 'weeks[' + index + '][teachers][]');
            });
        }

        // AJAX Form Submission
        $('#dutyForm').on('submit', function(e) {
            e.preventDefault();
            
            if ($('#weeks-container .week-card').length === 0) {
                alert("Please add at least one week.");
                return;
            }

            let $btn = $('#btnSaveRoster');
            $btn.prop('disabled', true).text('Saving...');
            
            $.ajax({
                url: "{{ route('admin.teacher_duties.store') }}",
                method: "POST",
                data: $(this).serialize(),
                timeout: 60000, 
                success: function(response) {
                    if (response.success) {
                        $('#assignDutyModal').modal('hide');
                        showAlert('success', response.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('danger', response.message || 'Error occurred');
                        $btn.prop('disabled', false).text('Save Roster & Send SMS');
                    }
                },
                error: function(xhr, status, error) {
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : (status === 'timeout' ? 'Request timed out. The SMS might be taking too long, but the roster might have been saved. Please refresh.' : 'Server error');
                    showAlert('danger', msg);
                    $btn.prop('disabled', false).text('Save Roster & Send SMS');
                }
            });
        });

        // EDIT FUNCTIONALITY
        $(document).on('click', '.edit-duty', function(e) {
            e.preventDefault();
            const startDate = $(this).data('start');
            const teacherIds = $(this).data('teachers'); 

            $('#dutyForm')[0].reset();
            $('.modal-title').text('Update Teacher Duty');
            $('#weeks-container').empty();
            $('#no-weeks-msg').hide();
            $('#continuityPrompt').hide(); 
            $('#add-week-btn').hide(); 
            
            $('#start_date').val(startDate).attr('readonly', true);
            
            addWeekRow(); // Using the correct function name
            const $firstWeek = $('#weeks-container .week-card').first();
            $firstWeek.find('.week-title').text('Editing Week (' + startDate + ')');
            $firstWeek.find('.remove-week-btn').hide(); 
            
            const $teacherList = $firstWeek.find('.week-teachers-list');
            $teacherList.empty(); // Clear default one added by addWeekRow
            
            if(Array.isArray(teacherIds)) {
                teacherIds.forEach(id => {
                    let template = $('#teacher-select-template').html();
                    let $row = $(template);
                    $row.find('select').attr('name', 'weeks[0][teachers][]').val(id);
                    $teacherList.append($row);
                });
            }

            $('#assignDutyModal').modal('show');
        });

        // DELETE FUNCTIONALITY
        $(document).on('click', '.delete-duty', function(e) {
            e.preventDefault();
            const startDate = $(this).data('start');
            const endDate = $(this).data('end');

            if (!confirm(`Are you sure you want to delete the duties for the week of ${startDate}?`)) return;

            $.ajax({
                url: "{{ route('admin.teacher_duties.destroy') }}",
                method: "DELETE",
                data: {
                    _token: "{{ csrf_token() }}",
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        $(`#duty-row-${startDate}`).fadeOut(300, function() { $(this).remove(); });
                    } else {
                        showAlert('danger', response.message || 'Error occurred');
                    }
                },
                error: function() {
                    showAlert('danger', 'Failed to delete record.');
                }
            });
        });

        // Open Modal for New Assignment
        $('#openAssignModal').click(function() {
            $('.modal-title').text('Assign Teacher Duties');
            $('#dutyForm')[0].reset();
            $('#weeks-container').empty();
            $('#no-weeks-msg').show();
            $('#continuityPrompt').show(); 
            $('#add-week-btn').show();
            
            // Re-apply the initial date if it was set
            @if($lastDutyEndDate)
                $('#start_date').val(moment(lastEndDate).add(1, 'days').format('YYYY-MM-DD')).attr('readonly', true);
                $('#optContinue').parent().addClass('active').find('input').prop('checked', true);
                $('#optNewDate').parent().removeClass('active').find('input').prop('checked', false);
            @else
                $('#start_date').val('').attr('readonly', false);
            @endif

            $('#assignDutyModal').modal('show');
        });

        // FILTERING FUNCTIONALITY
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            const fromDate = $('#filter_from').val();
            const toDate = $('#filter_to').val();

            if (!fromDate || !toDate) {
                alert("Please select both dates.");
                return;
            }

            let $btn = $(this).find('button[type="submit"]');
            let originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Filtering...');

            // AJAX refresh table body and header
            $.ajax({
                url: "{{ route('admin.teacher_duties') }}",
                data: { from_date: fromDate, to_date: toDate },
                success: function(response) {
                    $('#dutiesTableBody').html(response.html);
                    
                    // Update Page Headers
                    $('.breadcrumbs h1').html(response.title);
                    $('.card-header .card-title').text('Duties Roster - ' + response.title.split(' in ')[1]);
                },
                error: function() {
                    showAlert('danger', 'Failed to retrieve filtered data.');
                },
                complete: function() {
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });

        $('#resetFilters').click(function() {
            // Reload page to reset to current month
            window.location.href = "{{ route('admin.teacher_duties') }}";
        });

        $('#downloadPdf').click(function() {
            const fromDate = $('#filter_from').val();
            const toDate = $('#filter_to').val();
            
            if (!fromDate || !toDate) {
                alert("Please select date range for export.");
                return;
            }

            let $btn = $(this);
            let originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generating PDF...');

            // Fetch PDF via AJAX to show loading state
            fetch("{{ route('admin.teacher_duties.export_pdf') }}?from_date=" + fromDate + "&to_date=" + toDate)
                .then(response => {
                    if(!response.ok) throw new Error('Network response was not ok');
                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = 'Teacher_Duty_Roster_' + fromDate + '_to_' + toDate + '.pdf';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                })
                .catch(error => {
                    console.error('Download error:', error);
                    showAlert('danger', 'Failed to generate PDF. Please try again.');
                })
                .finally(() => {
                    $btn.prop('disabled', false).html(originalText);
                });
        });
        // Duty Book Row Expansion Icon Toggle
        $(document).on('click', '.expandable-row', function() {
            $(this).find('.transition-icon').toggleClass('fa-chevron-right fa-chevron-down');
        });

        // Event for auto-calculating totals in the modal table (copied from teacher side)
        $(document).on('input', '#attendance_table input', function() {
            let $row = $(this).closest('tr');
            const categories = ['.reg', '.pres', '.shift', '.new', '.abs', '.perm', '.sick'];
            categories.forEach(cat => {
                let b = parseInt($row.find(cat + '-b').val()) || 0;
                let g = parseInt($row.find(cat + '-g').val()) || 0;
                $row.find(cat + '-t').val(b + g);
            });
            calculateModalFooterTotals();
        });

        function calculateModalFooterTotals() {
            const columns = ['.reg-b', '.reg-g', '.reg-t', '.pres-b', '.pres-g', '.pres-t', '.shift-b', '.shift-g', '.shift-t', '.new-b', '.new-g', '.new-t', '.abs-b', '.abs-g', '.abs-t', '.perm-b', '.perm-g', '.perm-t', '.sick-b', '.sick-g', '.sick-t'];
            columns.forEach((selector, index) => {
                let sum = 0;
                $('#dutyReportModal ' + selector).each(function() {
                    sum += parseInt($(this).val()) || 0;
                });
                $('#dutyReportModal #total-' + index).text(sum);
            });
        }

        // View & Sign Report Action
        $(document).on('click', '.view-sign-report, .view-report-admin', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $btn = $(this);
            const reportID = $btn.data('id');
            const date = $btn.data('date');
            const isSigning = $btn.hasClass('view-sign-report');

            console.log('Click detected:', { reportID, date, isSigning });

            try {
                // Initialize Modal State
                $('#dutyReportModal').modal('show');
                $('#dailyDutyForm')[0].reset();
                $('#display_teacher_name').text('Loading report data...');
                $('#reportID').val(reportID || '');
                $('#report_date').val(date || '');
                
                if (typeof moment !== 'undefined' && date) {
                    $('#display_date').text(moment(date).format('DD/MM/YYYY'));
                    $('#display_day').text(moment(date).format('dddd').toUpperCase());
                } else {
                    $('#display_date').text(date || '---');
                }

                $('#dailyDutyForm input, #dailyDutyForm textarea').prop('readonly', true);
                $('#dailyDutyForm input[type="number"]').css('background-color', '#f8f9fa');
                $('#saveDraft, #saveAndSend, #syncFromAttendance').hide();
                $('#downloadReportPdf').show();
                
                if (isSigning) {
                    $('#adminFeedbackSection').show();
                    $('#btnApproveReport').show();
                    $('#admin_comments, #signed_by').prop('readonly', false).css('background-color', '#fff');
                    $('#signedAtDisplay').hide();
                } else {
                    $('#adminFeedbackSection').show();
                    $('#btnApproveReport').hide();
                    $('#admin_comments, #signed_by').prop('readonly', true).css('background-color', '#f8f9fa');
                }

                // Load Data
                $.get("{{ route('teacher.duty_book.report') }}", { 
                    date: date,
                    reportID: reportID
                }, function(response) {
                    window.totalActiveStudentsInSchool = response.total_active_students || 0;
                    if (response.success && response.report) {
                        const report = response.report;
                        $('#reportID').val(report.reportID);
                        $('#display_teacher_name').text(report.teacher_name || '---');
                        $('input[name="attendance_percentage"]').val(report.attendance_percentage);
                        $('input[name="school_environment"]').val(report.school_environment);
                        $('input[name="pupils_cleanliness"]').val(report.pupils_cleanliness);
                        $('textarea[name="teachers_attendance"]').val(report.teachers_attendance);
                        $('input[name="timetable_status"]').val(report.timetable_status);
                        $('input[name="outside_activities"]').val(report.outside_activities);
                        $('input[name="special_events"]').val(report.special_events);
                        $('textarea[name="teacher_comments"]').val(report.teacher_comments);
                        
                        $('#admin_comments').val(report.admin_comments);
                        $('#signed_by').val(report.signed_by);

                        // Digital Signature Display Logic
                        if (report.signature_image) {
                            $('#signature-image-preview').attr('src', report.signature_image);
                            $('#view-only-signature').show();
                            $('#signature-pad, #clear-signature').hide();
                        } else if (isSigning) {
                            $('#view-only-signature').hide();
                            $('#signature-pad, #clear-signature').show();
                            if(signaturePad) signaturePad.clear();
                        } else {
                            $('#view-only-signature').hide();
                            $('#signature-pad, #clear-signature').hide();
                        }

                        if (report.signed_at && typeof moment !== 'undefined') {
                            $('#signedAtDate').text(moment(report.signed_at).format('DD/MM/YYYY HH:mm'));
                            $('#signedAtDisplay').show();
                        }

                        if (report.attendance_data) {
                            let data = typeof report.attendance_data === 'string' ? JSON.parse(report.attendance_data) : report.attendance_data;
                            $('.class-row').each(function() {
                                let cid = $(this).data('class-id');
                                if (data[cid]) {
                                    $(this).find('.reg-b').val(data[cid].reg_b);
                                    $(this).find('.reg-g').val(data[cid].reg_g);
                                    $(this).find('.pres-b').val(data[cid].pres_b);
                                    $(this).find('.pres-g').val(data[cid].pres_g);
                                    $(this).find('.shift-b').val(data[cid].shift_b || 0);
                                    $(this).find('.shift-g').val(data[cid].shift_g || 0);
                                    $(this).find('.new-b').val(data[cid].new_b || 0);
                                    $(this).find('.new-g').val(data[cid].new_g || 0);
                                    $(this).find('.abs-b').val(data[cid].abs_b || 0);
                                    $(this).find('.abs-g').val(data[cid].abs_g || 0);
                                    $(this).find('.perm-b').val(data[cid].perm_b || 0);
                                    $(this).find('.perm-g').val(data[cid].perm_g || 0);
                                    $(this).find('.sick-b').val(data[cid].sick_b || 0);
                                    $(this).find('.sick-g').val(data[cid].sick_g || 0);
                                }
                            });
                            refreshModalTableTotals();
                        }
                    } else {
                        Swal.fire('Info', 'Could not load report details.', 'info');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Server connection failed.', 'error');
                });
            } catch (err) {
                console.error('Error in View/Sign handler:', err);
                alert("An error occurred while opening the report.");
            }
        });

        // Admin Approval/Sign logic
        $('#btnApproveReport').click(function() {
            const reportID = $('#reportID').val();
            const signedBy = $('#signed_by').val();
            const comments = $('#admin_comments').val();
            
            if (signaturePad && signaturePad.isEmpty()) {
                Swal.fire('Signature Drawing Required', 'Please draw your signature in the signature area.', 'warning');
                return;
            }

            if (!signedBy) {
                Swal.fire('Name Required', 'Please type your full name below the signature area.', 'warning');
                return;
            }

            const signatureImage = signaturePad ? signaturePad.toDataURL() : null;

            Swal.fire({
                title: 'Signing Report...',
                text: 'Please wait while we record your signature and approve the report.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.post("{{ route('admin.duty_book.approve') }}", {
                _token: "{{ csrf_token() }}",
                reportID: reportID,
                signed_by: signedBy,
                admin_comments: comments,
                signature_image: signatureImage
            }, function(response) {
                Swal.fire({
                    title: 'Approved!',
                    text: response.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            }).fail(function(err) {
                let msg = 'Failed to approve report.';
                if (err.responseJSON && err.responseJSON.message) msg = err.responseJSON.message;
                Swal.fire('Error', msg, 'error');
            });
        });

        // Download PDF Handler (Individual Report) using AJAX
        $('#downloadReportPdf').click(function() {
            let date = $('#report_date').val();
            const $btn = $(this);
            const originalHtml = $btn.html();
            
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generating PDF...');

            fetch("{{ route('teacher.duty_book.export_report') }}?date=" + date)
                .then(response => {
                    if(!response.ok) throw new Error('Network response was not ok');
                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = 'Daily_Duty_Report_' + date + '.pdf';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    Swal.fire({
                        title: 'Downloaded',
                        text: 'Report generated successfully.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                })
                .catch(error => {
                    console.error('Download error:', error);
                    showAlert('danger', 'Failed to generate PDF. Please try again.');
                })
                .finally(() => {
                    $btn.prop('disabled', false).html(originalHtml);
                });
        });

        // Attendance Table Calculation Logic
        $(document).on('input', '#attendance_table input', function() {
            let $row = $(this).closest('tr');
            const categories = ['.reg', '.pres', '.shift', '.new', '.abs', '.perm', '.sick'];
            categories.forEach(cat => {
                let b = parseInt($row.find(cat + '-b').val()) || 0;
                let g = parseInt($row.find(cat + '-g').val()) || 0;
                $row.find(cat + '-t').val(b + g);
            });
            calculateModalFooterTotals();
        });

        function calculateModalFooterTotals() {
            const columns = [
                '.reg-b', '.reg-g', '.reg-t',
                '.pres-b', '.pres-g', '.pres-t',
                '.shift-b', '.shift-g', '.shift-t',
                '.new-b', '.new-g', '.new-t',
                '.abs-b', '.abs-g', '.abs-t',
                '.perm-b', '.perm-g', '.perm-t',
                '.sick-b', '.sick-g', '.sick-t'
            ];

            columns.forEach((selector, index) => {
                let sum = 0;
                $(selector).each(function() {
                    sum += parseInt($(this).val()) || 0;
                });
                $('#total-' + index).text(sum);
            });

            // Recalculate percentage if totalActiveStudents is available
            let totalPres = parseInt($('#total-5').text()) || 0;
            let totalActive = window.totalActiveStudentsInSchool || 0;
            if (totalActive > 0) {
                let perc = (totalPres / totalActive) * 100;
                $('#attendance_percentage').val(perc.toFixed(2));
            }
        }

        function refreshModalTableTotals() {
            $('.class-row').each(function() {
                let $row = $(this);
                const categories = ['.reg', '.pres', '.shift', '.new', '.abs', '.perm', '.sick'];
                categories.forEach(cat => {
                    let b = parseInt($row.find(cat + '-b').val()) || 0;
                    let g = parseInt($row.find(cat + '-g').val()) || 0;
                    $row.find(cat + '-t').val(b + g);
                });
            });
            calculateModalFooterTotals();
        }


        function showAlert(type, message) {
            let html = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
            $('#alertPlaceholder').html(html);
        }
    });
</script>
@endpush
