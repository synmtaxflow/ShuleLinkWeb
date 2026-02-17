@include('includes.teacher_nav')

<div class="breadcrumbs">
    <div class="col-sm-6">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Teacher Duty Book - <u>{{ $monthTitle }}</u></h1>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('teachersDashboard') }}">Dashboard</a></li>
                    <li class="active">Duty Book</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="animated fadeIn">
        @if($currentDuty)
        <div class="alert alert-success shadow-sm mb-4" style="border-left: 5px solid #28a745;">
            <div class="d-flex align-items-center">
                <div class="mr-3">
                    <i class="fa fa-calendar-check-o fa-3x text-success"></i>
                </div>
                <div>
                    <h4 class="alert-heading text-success mb-1">Your Duty Status: Active</h4>
                    <p class="mb-0">You are on duty from <strong>{{ \Carbon\Carbon::parse($currentDuty->start_date)->format('d M') }}</strong> to <strong>{{ \Carbon\Carbon::parse($currentDuty->end_date)->format('d M Y') }}</strong>.</p>
                </div>
            </div>
        </div>
        @endif

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="card-title mb-0">My Assigned Duty Weeks</strong>
                            <div class="d-flex align-items-center">
                                <form action="{{ route('teacher.duty_book') }}" method="GET" class="form-inline mr-3">
                                    <input type="date" name="from_date" class="form-control form-control-sm mr-2" value="{{ $fromDate }}">
                                    <input type="date" name="to_date" class="form-control form-control-sm mr-2" value="{{ $toDate }}">
                                    <button type="submit" class="btn btn-sm btn-info">Filter</button>
                                </form>
                                <a href="{{ route('teacher.duty_book.export', ['from_date' => $fromDate, 'to_date' => $toDate]) }}" class="btn btn-sm btn-danger">
                                    <i class="fa fa-file-pdf-o"></i> Export Roster
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Week</th>
                                        <th>Teachers on Duty</th>
                                        <th>Dates</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php 
                                        $groupedDuties = $duties->groupBy(function($item) {
                                            return $item->start_date . '_' . $item->end_date;
                                        });
                                        $weekCounter = 1;
                                        $todayDate = \Carbon\Carbon::today();
                                    @endphp
                                    @foreach($groupedDuties as $key => $weekGroup)
                                        @php
                                            $startDate = \Carbon\Carbon::parse($weekGroup[0]->start_date);
                                            $endDate = \Carbon\Carbon::parse($weekGroup[0]->end_date);
                                            $isActiveWeek = $todayDate->between($startDate->copy()->startOfDay(), $endDate->copy()->endOfDay());
                                        @endphp
                                        <tr class="duty-week-row {{ $isActiveWeek ? 'table-primary active-week' : '' }}" 
                                            data-start="{{ $startDate->format('Y-m-d') }}" 
                                            data-end="{{ $endDate->format('Y-m-d') }}"
                                            style="cursor: pointer;">
                                            <td>
                                                Week {{ $weekCounter++ }}
                                                @if($isActiveWeek)
                                                    <span class="badge badge-success ml-2">ACTIVE WEEK</span>
                                                @endif
                                            </td>
                                            <td>
                                                @foreach($weekGroup as $duty)
                                                    <span class="badge {{ $duty->teacherID == Session::get('teacherID') ? 'badge-primary' : 'badge-light' }} p-2 mb-1">
                                                        <i class="fa fa-user"></i> {{ $duty->teacher ? $duty->teacher->first_name . ' ' . $duty->teacher->last_name : 'N/A' }}
                                                    </span>
                                                @endforeach
                                            </td>
                                            <td>
                                                {{ $startDate->format('d M') }} - {{ $endDate->format('d M Y') }}
                                            </td>
                                            <td>
                                                <i class="fa fa-chevron-down"></i>
                                            </td>
                                        </tr>
                                        <!-- Expandable Day View -->
                                        <tr class="day-view-container" id="days-{{ $startDate->format('Y-m-d') }}" style="display: none; background: #fdfdfd;">
                                            <td colspan="4" class="p-0">
                                                <div class="p-3">
                                                    <table class="table table-sm table-bordered bg-white shadow-sm">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th>Day</th>
                                                                <th>Date</th>
                                                                <th>Report Status</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @for($i = 0; $i < 5; $i++)
                                                                @php
                                                                    $currentDay = $startDate->copy()->addDays($i);
                                                                    $isToday = $todayDate->isSameDay($currentDay);
                                                                    $dayName = $currentDay->format('l');
                                                                    $dateStr = $currentDay->format('Y-m-d');
                                                                    $report = $reports[$dateStr] ?? null;
                                                                    $isFuture = $currentDay->isAfter($todayDate);
                                                                @endphp
                                                                <tr class="{{ $isToday ? 'table-warning' : '' }}">
                                                                    <td>{{ $dayName }}</td>
                                                                    <td>{{ $currentDay->format('d/m/Y') }}</td>
                                                                    <td>
                                                                        @if($report)
                                                                            @if($report->status === 'Approved')
                                                                                <span class="badge badge-success"><i class="fa fa-check-circle"></i> Approved</span>
                                                                            @elseif($report->status === 'Sent')
                                                                                <span class="badge badge-primary"><i class="fa fa-paper-plane"></i> Sent (Pending)</span>
                                                                            @else
                                                                                <span class="badge badge-info"><i class="fa fa-pencil"></i> Draft</span>
                                                                            @endif
                                                                        @elseif($isFuture)
                                                                            <span class="badge badge-light">Upcoming</span>
                                                                        @else
                                                                            <span class="badge badge-secondary">Waiting</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if($isFuture)
                                                                            <small class="text-muted"><i class="fa fa-lock"></i> Available on {{ $currentDay->format('d M') }}</small>
                                                                        @else
                                                                             <div class="btn-group">
                                                                                @if($report)
                                                                                    <button class="btn btn-sm btn-outline-info view-report" data-date="{{ $dateStr }}" data-day="{{ $dayName }}" data-mode="view">
                                                                                        <i class="fa fa-eye"></i> View Filled Form
                                                                                    </button>
                                                                                    @if($report->status !== 'Approved' && $report->status !== 'Sent')
                                                                                        <button class="btn btn-sm btn-info view-report" data-date="{{ $dateStr }}" data-day="{{ $dayName }}" data-mode="edit">
                                                                                            <i class="fa fa-edit"></i> Fill Form
                                                                                        </button>
                                                                                    @endif
                                                                                @else
                                                                                    <button class="btn btn-sm btn-info view-report" data-date="{{ $dateStr }}" data-day="{{ $dayName }}" data-mode="edit">
                                                                                        <i class="fa fa-plus"></i> Fill Form
                                                                                    </button>
                                                                                @endif

                                                                                @if(!$report || ($report->status !== 'Approved' && $report->status !== 'Sent'))
                                                                                    <button class="btn btn-sm btn-success send-to-admin" data-date="{{ $dateStr }}">
                                                                                        <i class="fa fa-paper-plane"></i> Send to Admin
                                                                                    </button>
                                                                                @endif
                                                                            </div>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endfor
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if($groupedDuties->isEmpty())
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">You have no duty assignments for this period.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Daily Duty Report Modal -->
<div class="modal fade" id="dutyReportModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold">
                    <i class="fa fa-file-text-o"></i> Daily Duty Report 
                    <span id="modal_status_badge" class="ml-2"></span>
                    <div style="font-size: 0.8rem; font-weight: normal; opacity: 0.9;">Teacher on Duty: {{ Session::get('full_name') }}</div>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" style="background: #f4f7f6;">
                <form id="dailyDutyForm">
                    @csrf
                    <input type="hidden" name="report_date" id="report_date">
                    
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <div class="alert alert-info py-2 px-3 border-0 shadow-sm mb-4">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <h6 class="mb-0"><strong>Teacher on Duty:</strong> <u>{{ Session::get('full_name') }}</u></h6>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h6 class="mb-0"><strong>Day:</strong> <span id="display_day" class="text-uppercase text-primary"></span></h6>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <h6 class="mb-0"><strong>Date:</strong> <span id="display_date" class="font-weight-bold"></span></h6>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered text-center" id="attendance_table">
                                    <thead class="bg-light">
                                        <tr>
                                            <th rowspan="2" class="align-middle">CLASS</th>
                                            <th colspan="3">REGISTERED</th>
                                            <th colspan="3">PRESENT</th>
                                            <th colspan="3">SHIFTED</th>
                                            <th colspan="3">NEW COMERS</th>
                                            <th colspan="3">ABSENT</th>
                                            <th colspan="3">PERMISSION</th>
                                            <th colspan="3">SICK</th>
                                        </tr>
                                        <tr style="font-size: 0.7rem;">
                                            @for($i=0; $i<7; $i++)
                                                <th>B</th><th>G</th><th>T</th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($classes as $cls)
                                        <tr class="class-row" data-class-id="{{ $cls->classID }}">
                                            <td class="bg-light font-weight-bold">{{ $cls->class_name }}</td>
                                            <!-- REGISTERED -->
                                            <td><input type="number" class="form-control form-control-sm reg-b" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm reg-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm reg-t" readonly value="0"></td>
                                            <!-- PRESENT -->
                                            <td><input type="number" class="form-control form-control-sm pres-b" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm pres-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm pres-t" readonly value="0"></td>
                                            <!-- SHIFTED -->
                                            <td><input type="number" class="form-control form-control-sm shift-b" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm shift-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm shift-t" readonly value="0"></td>
                                            <!-- NEW COMERS -->
                                            <td><input type="number" class="form-control form-control-sm new-b" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm new-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm new-t" readonly value="0"></td>
                                            <!-- ABSENT -->
                                            <td><input type="number" class="form-control form-control-sm abs-b" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm abs-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm abs-t" readonly value="0"></td>
                                            <!-- PERMISSION -->
                                            <td><input type="number" class="form-control form-control-sm perm-b" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm perm-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm perm-t" readonly value="0"></td>
                                            <!-- SICK -->
                                            <td><input type="number" class="form-control form-control-sm sick-b" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm sick-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm sick-t" readonly value="0"></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light font-weight-bold">
                                            <td>TOTAL</td>
                                            @for($i=0; $i<21; $i++)
                                                <td id="total-{{ $i }}">0</td>
                                            @endfor
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 border-right">
                                    <div class="form-group mb-3">
                                        <label>1. Attendance report in percentage (%):</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.01" name="attendance_percentage" id="attendance_percentage" class="form-control">
                                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>2. School environment:</label>
                                        <input type="text" name="school_environment" class="form-control form-control-sm" placeholder="e.g. Well cleaned">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>3. Pupil's cleanliness:</label>
                                        <input type="text" name="pupils_cleanliness" class="form-control form-control-sm" placeholder="e.g. They were smart">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>4. Teacher's attendance:</label>
                                        <textarea name="teachers_attendance" class="form-control form-control-sm" rows="2" placeholder="i) Name... ii) Name..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>5. Time table:</label>
                                        <input type="text" name="timetable_status" class="form-control form-control-sm" placeholder="e.g. Well followed">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>6. Outside activities:</label>
                                        <input type="text" name="outside_activities" class="form-control form-control-sm" placeholder="e.g. Well conducted">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>7. Special events:</label>
                                        <input type="text" name="special_events" class="form-control form-control-sm">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>8. Teachers' on duty comments:</label>
                                        <textarea name="teacher_comments" class="form-control form-control-sm" rows="2" placeholder="Sum up the day..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" id="syncFromAttendance" style="display: none;">
                        <i class="fa fa-refresh"></i> Sync From Attendance
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-danger" id="downloadReportPdf" style="display: none;">
                        <i class="fa fa-file-pdf-o"></i> Download PDF
                    </button>
                    <button type="button" class="btn btn-info" id="saveDraft">Save as Draft</button>
                    <button type="button" class="btn btn-primary" id="saveAndSend">Send to Admin</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card-header { border-bottom: 2px solid #f8f9fa; }
    .table thead th { border-top: 0; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
    .badge { border-radius: 4px; }
    .table-primary { background-color: rgba(0, 123, 255, 0.05) !important; }
    .duty-week-row:hover { background-color: #f1f4f9; }
    .active-week { border-left: 4px solid #007bff; }
    .modal-xl { max-width: 95%; }
    #attendance_table input { width: 50px; text-align: center; border: 1px solid #eee; padding: 2px; }
    #attendance_table input:focus { border-color: #007bff; outline: none; box-shadow: 0 0 5px rgba(0,123,255,0.2); }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Toggle week expansion
    $('.duty-week-row').click(function() {
        let start = $(this).data('start');
        let $child = $('#days-' + start);
        $('.day-view-container').not($child).hide(); // Close others
        $child.toggle();
        $(this).find('i.fa').toggleClass('fa-chevron-down fa-chevron-up');
    });

    // Handle inputs for auto-totals
    $(document).on('input', '#attendance_table input', function() {
        let $row = $(this).closest('tr');
        
        // Sum boys + girls for each category in the row
        const categories = ['.reg', '.pres', '.shift', '.new', '.abs', '.perm', '.sick'];
        categories.forEach(cat => {
            let b = parseInt($row.find(cat + '-b').val()) || 0;
            let g = parseInt($row.find(cat + '-g').val()) || 0;
            $row.find(cat + '-t').val(b + g);
        });

        calculateFooterTotals();
    });

    function calculateFooterTotals() {
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

        // Calculate auto-percentage based on school-wide totals
        let totalPres = parseInt($('#total-5').text()) || 0;
        let totalActiveInSchool = window.totalActiveStudentsInSchool || 0;
        
        if (totalActiveInSchool > 0) {
            let perc = (totalPres / totalActiveInSchool) * 100;
            $('#attendance_percentage').val(perc.toFixed(2));
        }
    }

    // View/Fill Form
    $('.view-report').click(function() {
        let date = $(this).data('date');
        let day = $(this).data('day');
        let mode = $(this).data('mode'); // edit or view
        
        $('#report_date').val(date);
        $('#display_day').text(day.toUpperCase());
        $('#display_date').text(date);
        
        // Modal State Reset
        $('#dailyDutyForm')[0].reset();
        $('#modal_status_badge').html('');
        
            if (mode === 'view') {
                $('#dailyDutyForm input, #dailyDutyForm textarea').prop('readonly', true);
                $('#dailyDutyForm input[type="number"]').css('background-color', '#f8f9fa');
                $('#saveDraft, #saveAndSend, #syncFromAttendance').hide();
                $('#downloadReportPdf').show();
            } else {
                $('#dailyDutyForm input, #dailyDutyForm textarea').prop('readonly', false);
                $('#dailyDutyForm input[type="number"]').css('background-color', '#fff');
                $('#saveDraft, #saveAndSend, #syncFromAttendance').show();
                $('#downloadReportPdf').hide();
            }

        // Load existing report
        $.get("{{ route('teacher.duty_book.report') }}", { date: date }, function(response) {
            let report = response.report;
            let systemData = response.system_attendance;
            window.totalActiveStudentsInSchool = response.total_active_students || 0;

            if (report) {
                // Set Status Badge
                let badgeClass = 'badge-secondary';
                if (report.status === 'Approved') badgeClass = 'badge-success';
                else if (report.status === 'Sent') badgeClass = 'badge-primary';
                else if (report.status === 'Draft') badgeClass = 'badge-info';
                
                $('#modal_status_badge').html('<span class="badge ' + badgeClass + '">' + report.status + '</span>');

                // Load saved report data
                $('input[name="attendance_percentage"]').val(report.attendance_percentage);
                $('input[name="school_environment"]').val(report.school_environment);
                $('input[name="pupils_cleanliness"]').val(report.pupils_cleanliness);
                $('textarea[name="teachers_attendance"]').val(report.teachers_attendance);
                $('input[name="timetable_status"]').val(report.timetable_status);
                $('input[name="outside_activities"]').val(report.outside_activities);
                $('input[name="special_events"]').val(report.special_events);
                $('textarea[name="teacher_comments"]').val(report.teacher_comments);
                
                // Load attendance grid from report
                if (report.attendance_data) {
                    let data = JSON.parse(report.attendance_data);
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
                }
            } else {
                $('#modal_status_badge').html('<span class="badge badge-light">NEW REPORT</span>');
                if (systemData) {
                    // New report: Pre-fill with system defaults
                    $('.class-row').each(function() {
                        let cid = $(this).data('class-id');
                        if (systemData[cid]) {
                            $(this).find('.reg-b').val(systemData[cid].reg_b);
                            $(this).find('.reg-g').val(systemData[cid].reg_g);
                            $(this).find('.pres-b').val(systemData[cid].pres_b);
                            $(this).find('.pres-g').val(systemData[cid].pres_g);
                            $(this).find('.abs-b').val(systemData[cid].abs_b);
                            $(this).find('.abs-g').val(systemData[cid].abs_g);
                            $(this).find('.perm-b').val(systemData[cid].perm_b);
                            $(this).find('.perm-g').val(systemData[cid].perm_g);
                            $(this).find('.new-b').val(systemData[cid].new_b);
                            $(this).find('.new-g').val(systemData[cid].new_g);
                            $(this).find('.sick-b').val(systemData[cid].sick_b);
                            $(this).find('.sick-g').val(systemData[cid].sick_g);
                        }
                    });
                }
            }

            // Trigger calculation for totals/sums
            $('.class-row').first().find('input').first().trigger('input');
            $('#dutyReportModal').modal('show');
        });
    });

    // Sync From Attendance Button Logic
    $('#syncFromAttendance').click(function() {
        let date = $('#report_date').val();
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Syncing...');

        $.get("{{ route('teacher.duty_book.report') }}", { date: date }, function(response) {
            let systemData = response.system_attendance;
            if (systemData) {
                $('.class-row').each(function() {
                    let cid = $(this).data('class-id');
                    if (systemData[cid]) {
                        $(this).find('.reg-b').val(systemData[cid].reg_b);
                        $(this).find('.reg-g').val(systemData[cid].reg_g);
                        $(this).find('.pres-b').val(systemData[cid].pres_b);
                        $(this).find('.pres-g').val(systemData[cid].pres_g);
                        $(this).find('.abs-b').val(systemData[cid].abs_b);
                        $(this).find('.abs-g').val(systemData[cid].abs_g);
                        $(this).find('.perm-b').val(systemData[cid].perm_b);
                        $(this).find('.perm-g').val(systemData[cid].perm_g);
                        $(this).find('.new-b').val(systemData[cid].new_b);
                        $(this).find('.new-g').val(systemData[cid].new_g);
                        $(this).find('.sick-b').val(systemData[cid].sick_b);
                        $(this).find('.sick-g').val(systemData[cid].sick_g);
                    }
                });
                $('.class-row').first().find('input').first().trigger('input');
                Swal.fire('Synced', 'Attendance counts updated from latest system data.', 'success');
            }
            $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Sync From Attendance');
        }).fail(function() {
            Swal.fire('Error', 'Failed to sync data.', 'error');
            $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Sync From Attendance');
        });
    });

    // Download Individual Report PDF
    $('#downloadReportPdf').click(function() {
        let date = $('#report_date').val();
        window.location.href = "{{ route('teacher.duty_book.export_report') }}?date=" + date;
    });

    // Save Draft or Send
    $('#saveDraft, #saveAndSend').click(function() {
        let action = $(this).attr('id') === 'saveAndSend' ? 'send' : 'draft';
        let attendanceData = {};
        
        $('.class-row').each(function() {
            let cid = $(this).data('class-id');
            attendanceData[cid] = {
                reg_b: $(this).find('.reg-b').val(),
                reg_g: $(this).find('.reg-g').val(),
                pres_b: $(this).find('.pres-b').val(),
                pres_g: $(this).find('.pres-g').val(),
                shift_b: $(this).find('.shift-b').val(),
                shift_g: $(this).find('.shift-g').val(),
                new_b: $(this).find('.new-b').val(),
                new_g: $(this).find('.new-g').val(),
                abs_b: $(this).find('.abs-b').val(),
                abs_g: $(this).find('.abs-g').val(),
                perm_b: $(this).find('.perm-b').val(),
                perm_g: $(this).find('.perm-g').val(),
                sick_b: $(this).find('.sick-b').val(),
                sick_g: $(this).find('.sick-g').val()
            };
        });

        let formData = {
            _token: "{{ csrf_token() }}",
            report_date: $('#report_date').val(),
            attendance_data: JSON.stringify(attendanceData),
            attendance_percentage: $('#attendance_percentage').val(),
            school_environment: $('input[name="school_environment"]').val(),
            pupils_cleanliness: $('input[name="pupils_cleanliness"]').val(),
            teachers_attendance: $('textarea[name="teachers_attendance"]').val(),
            timetable_status: $('input[name="timetable_status"]').val(),
            outside_activities: $('input[name="outside_activities"]').val(),
            special_events: $('input[name="special_events"]').val(),
            teacher_comments: $('textarea[name="teacher_comments"]').val(),
            action: action
        };

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        $.post("{{ route('teacher.duty_book.save') }}", formData, function(response) {
            Swal.fire({
                title: 'Success',
                text: response.message,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload(); 
            });
        }).fail(function(err) {
            let msg = 'Failed to save report. Please check your network.';
            if (err.responseJSON && err.responseJSON.message) msg = err.responseJSON.message;
            Swal.fire('Error', msg, 'error');
            $btn.prop('disabled', false).html(action === 'send' ? 'Send to Admin' : 'Save as Draft');
        });
    });
    
    // Quick send from the table
    $('.send-to-admin').click(function() {
        let date = $(this).data('date');
        Swal.fire({
            title: 'Send to Admin?',
            text: 'Are you sure you want to finalize and send this report?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Send'
        }).then((res) => {
            if (res.isConfirmed) {
                $.post("{{ route('teacher.duty_book.save') }}", {
                    _token: "{{ csrf_token() }}",
                    report_date: date,
                    action: 'send'
                }, function(response) {
                    Swal.fire('Sent', 'Report sent successfully.', 'success');
                });
            }
        });
    });
});
</script>
