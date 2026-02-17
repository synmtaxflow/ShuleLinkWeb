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

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script>
    jQuery(document).ready(function($) {
        
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
