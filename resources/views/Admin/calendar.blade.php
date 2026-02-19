@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    :root {
        --primary-color: #940000;
        --primary-hover: #b30000;
    }

    body {
        background-color: #f8f9fa;
    }

    .calendar-container {
        padding: 30px;
    }

    .calendar-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
        color: white;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .calendar-header h2 {
        margin: 0;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
        width: 100%;
        overflow-x: hidden;
    }

    .calendar-container {
        overflow-x: hidden;
        max-width: 100%;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        transition: all 0.2s ease;
        border-top: 4px solid var(--primary-color);
        min-height: 140px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .stat-card:hover {
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        transform: translateY(-3px);
    }

    .stat-number {
        font-size: 3rem;
        font-weight: 700;
        color: var(--primary-color);
        margin: 10px 0;
        line-height: 1.2;
    }

    .stat-label {
        font-size: 1rem;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .year-selector {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .calendar-actions {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .btn-primary-custom {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }

    .btn-primary-custom:hover {
        background-color: var(--primary-hover);
        border-color: var(--primary-hover);
        color: white;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
        width: 100%;
        overflow-x: hidden;
    }

    .month-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        transition: all 0.2s ease;
        min-height: 350px;
    }

    .month-card:hover {
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        transform: translateY(-3px);
    }

    .month-title {
        font-weight: 700;
        font-size: 1.2rem;
        color: var(--primary-color);
        margin-bottom: 15px;
        text-align: center;
        padding-bottom: 12px;
        border-bottom: 3px solid var(--primary-color);
    }

    .month-working-days {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 15px;
        text-align: center;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .month-working-days .label {
        font-size: 0.85rem;
        opacity: 0.9;
        display: block;
        margin-bottom: 5px;
    }

    .month-working-days .number {
        font-size: 1.8rem;
        font-weight: 700;
    }

    .holiday-badge {
        background-color: #ffc107;
        color: #000;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        margin: 2px;
        display: inline-block;
    }

    .event-badge {
        background-color: #17a2b8;
        color: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        margin: 2px;
        display: inline-block;
    }

    .holidays-list, .events-list {
        max-height: 200px;
        overflow-y: auto;
    }

    .list-item {
        padding: 8px;
        margin-bottom: 5px;
        background: #f8f9fa;
        border-radius: 5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .list-item:hover {
        background: #e9ecef;
    }

    .list-item-actions {
        display: flex;
        gap: 5px;
    }

    .btn-sm {
        padding: 2px 8px;
        font-size: 0.75rem;
    }
</style>

<div class="container-fluid calendar-container">
    <div class="calendar-header">
        <h2>
            <i class="fa fa-calendar"></i>
            School Calendar - {{ $currentYear }}
        </h2>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Days</div>
            <div class="stat-number">{{ $stats['total_days'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Working Days</div>
            <div class="stat-number">{{ $stats['working_days'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Weekend Days</div>
            <div class="stat-number">{{ $stats['weekend_days'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Holiday Days</div>
            <div class="stat-number">{{ $stats['holiday_days'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Events</div>
            <div class="stat-number">{{ $stats['event_days'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Teacher Sessions/Year</div>
            <div class="stat-number">{{ $stats['total_sessions'] }}</div>
        </div>
    </div>

    <!-- All Holidays Widget -->
    <div class="card mb-4" style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);">
        <div class="card-header bg-primary-custom text-white">
            <h5 class="mb-0">
                <i class="fa fa-calendar-check"></i> All Holidays for {{ $currentYear }}
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Auto-Detected Holidays (Tanzania Public Holidays) -->
                <div class="col-md-6">
                    <h6 class="text-success mb-3">
                        <i class="fa fa-magic"></i> Auto-Detected Public Holidays
                    </h6>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Holiday Name</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($autoHolidays as $autoHoliday)
                                    <tr>
                                        <td>{{ $autoHoliday['date']->format('d/m/Y') }}</td>
                                        <td><strong>{{ $autoHoliday['name'] }}</strong></td>
                                        <td><span class="badge bg-success">{{ $autoHoliday['type'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Manual Holidays (School Defined) -->
                <div class="col-md-6">
                    <h6 class="text-warning mb-3">
                        <i class="fa fa-edit"></i> School Defined Holidays
                    </h6>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Date Range</th>
                                    <th>Holiday Name</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($holidays->count() > 0)
                                    @foreach($holidays as $holiday)
                                        <tr>
                                            <td>
                                                {{ \Carbon\Carbon::parse($holiday->start_date)->format('d/m/Y') }}
                                                @if($holiday->start_date != $holiday->end_date)
                                                    - {{ \Carbon\Carbon::parse($holiday->end_date)->format('d/m/Y') }}
                                                @endif
                                            </td>
                                            <td><strong>{{ $holiday->holiday_name }}</strong></td>
                                            <td><span class="badge bg-warning">{{ $holiday->type }}</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="editHoliday({{ $holiday->holidayID }})">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteHoliday({{ $holiday->holidayID }})">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            <i class="fa fa-info-circle"></i> No school holidays defined yet
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Year Selector & Actions -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="year-selector">
            <label for="yearSelect" class="form-label mb-0">Year:</label>
            <select class="form-select" id="yearSelect" style="width: 150px;">
                @for($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                    <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="calendar-actions">
            <button class="btn btn-primary-custom" onclick="showAddHolidayModal()">
                <i class="fa fa-plus"></i> Add Holiday
            </button>
            <button class="btn btn-success" onclick="showBulkHolidayModal()">
                <i class="fa fa-calendar-plus"></i> Add Multiple Holidays
            </button>
            <button class="btn btn-primary-custom" onclick="showAddEventModal()">
                <i class="fa fa-plus"></i> Add Event
            </button>
        </div>
    </div>

    <!-- Calendar Grid (12 Months) -->
    <div class="calendar-grid">
        @php
            $months = ['January', 'February', 'March', 'April', 'May', 'June', 
                      'July', 'August', 'September', 'October', 'November', 'December'];
        @endphp
        @foreach($months as $index => $month)
            @php
                $monthNum = $index + 1;
                // Get manual holidays for this month
                $monthHolidays = $holidays->filter(function($h) use ($currentYear, $monthNum) {
                    $start = \Carbon\Carbon::parse($h->start_date);
                    $end = \Carbon\Carbon::parse($h->end_date);
                    return ($start->year == $currentYear && $start->month == $monthNum) ||
                           ($end->year == $currentYear && $end->month == $monthNum) ||
                           ($start->year <= $currentYear && $end->year >= $currentYear && 
                            $start->month <= $monthNum && $end->month >= $monthNum);
                });
                
                // Get auto-detected holidays for this month
                $monthAutoHolidays = collect($autoHolidays)->filter(function($h) use ($currentYear, $monthNum) {
                    return $h['date']->year == $currentYear && $h['date']->month == $monthNum;
                });
                $monthEvents = $events->filter(function($e) use ($currentYear, $monthNum) {
                    return \Carbon\Carbon::parse($e->event_date)->year == $currentYear &&
                           \Carbon\Carbon::parse($e->event_date)->month == $monthNum;
                });
                $monthWorkingDays = $monthlyStats[$monthNum]['working_days'] ?? 0;
            @endphp
            <div class="month-card">
                <div class="month-title">{{ $month }}</div>
                <div class="month-working-days">
                    <span class="label">Working Days</span>
                    <span class="number">{{ $monthWorkingDays }}</span>
                </div>
                <div class="holidays-list">
                    <strong class="text-warning">School Holidays:</strong>
                    @if($monthHolidays->count() > 0)
                        @foreach($monthHolidays as $holiday)
                            <div class="list-item">
                                <div>
                                    <span class="holiday-badge">{{ $holiday->holiday_name }}</span>
                                    <small class="text-muted">
                                        ({{ \Carbon\Carbon::parse($holiday->start_date)->format('M d') }} - 
                                         {{ \Carbon\Carbon::parse($holiday->end_date)->format('M d') }})
                                    </small>
                                </div>
                                <div class="list-item-actions">
                                    <button class="btn btn-sm btn-info" onclick="editHoliday({{ $holiday->holidayID }})">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteHoliday({{ $holiday->holidayID }})">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted small">No school holidays</p>
                    @endif
                    
                    @if($monthAutoHolidays->count() > 0)
                        <strong class="text-success mt-2 d-block">Public Holidays:</strong>
                        @foreach($monthAutoHolidays as $autoHoliday)
                            <div class="list-item">
                                <div>
                                    <span class="badge bg-success">{{ $autoHoliday['name'] }}</span>
                                    <small class="text-muted">
                                        ({{ $autoHoliday['date']->format('M d') }})
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="events-list mt-2">
                    <strong class="text-info">Events:</strong>
                    @if($monthEvents->count() > 0)
                        @foreach($monthEvents as $event)
                            <div class="list-item">
                                <div>
                                    <span class="event-badge">{{ $event->event_name }}</span>
                                    <small class="text-muted">
                                        ({{ \Carbon\Carbon::parse($event->event_date)->format('M d') }})
                                    </small>
                                </div>
                                <div class="list-item-actions">
                                    <button class="btn btn-sm btn-info" onclick="editEvent({{ $event->eventID }})">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteEvent({{ $event->eventID }})">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted small">No events</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Add/Edit Holiday Modal -->
<div class="modal fade" id="holidayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="holidayModalTitle">Add Holiday</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="holidayForm">
                    <input type="hidden" id="holidayID" name="holidayID">
                    <div class="mb-3">
                        <label class="form-label">Holiday Name *</label>
                        <input type="text" class="form-control" id="holiday_name" name="holiday_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Date *</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Date *</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type *</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="Public Holiday">Public Holiday</option>
                            <option value="School Holiday">School Holiday</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" onclick="saveHoliday()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Add Holidays Modal -->
<div class="modal fade" id="bulkHolidayModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Multiple Holidays for {{ $currentYear }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> Add multiple holidays at once. Each holiday will appear in the calendar months.
                </div>
                <div id="bulkHolidaysContainer">
                    <div class="bulk-holiday-item mb-3 p-3 border rounded">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Holiday Name *</label>
                                <input type="text" class="form-control bulk-holiday-name" placeholder="e.g., Christmas Holiday" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label">Start Date *</label>
                                <input type="date" class="form-control bulk-start-date" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label">End Date *</label>
                                <input type="date" class="form-control bulk-end-date" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">Type *</label>
                                <select class="form-select bulk-holiday-type" required>
                                    <option value="Public Holiday">Public Holiday</option>
                                    <option value="School Holiday">School Holiday</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control bulk-description" rows="2" placeholder="Optional description"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-success" onclick="addBulkHolidayRow()">
                    <i class="fa fa-plus"></i> Add Another Holiday
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" onclick="saveBulkHolidays()">
                    <i class="fa fa-save"></i> Save All Holidays
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Event Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalTitle">Add Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="eventForm">
                    <input type="hidden" id="eventID" name="eventID">
                    <div class="mb-3">
                        <label class="form-label">Event Name *</label>
                        <input type="text" class="form-control" id="event_name" name="event_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Event Date *</label>
                        <input type="date" class="form-control" id="event_date" name="event_date" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="start_time" name="start_time">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Time</label>
                            <input type="time" class="form-control" id="end_time" name="end_time">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type *</label>
                        <select class="form-select" id="event_type" name="type" required>
                            <option value="School Event">School Event</option>
                            <option value="Sports">Sports</option>
                            <option value="Academic">Academic</option>
                            <option value="Cultural">Cultural</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="event_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_non_working_day" name="is_non_working_day">
                        <label class="form-check-label" for="is_non_working_day">
                            Non-Working Day (Exclude from working days calculation)
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" onclick="saveEvent()">Save</button>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Year selector change
    $('#yearSelect').on('change', function() {
        const year = $(this).val();
        window.location.href = '{{ route("admin.calendar") }}?year=' + year;
    });

    // Holiday Functions
    function showAddHolidayModal() {
        $('#holidayModalTitle').text('Add Holiday');
        $('#holidayForm')[0].reset();
        $('#holidayID').val('');
        new bootstrap.Modal(document.getElementById('holidayModal')).show();
    }

    function editHoliday(holidayID) {
        $.ajax({
            url: '/holidays/' + holidayID,
            method: 'GET',
            success: function(response) {
                $('#holidayModalTitle').text('Edit Holiday');
                $('#holidayID').val(response.holidayID);
                $('#holiday_name').val(response.holiday_name);
                $('#start_date').val(response.start_date);
                $('#end_date').val(response.end_date);
                $('#type').val(response.type);
                $('#description').val(response.description);
                new bootstrap.Modal(document.getElementById('holidayModal')).show();
            },
            error: function() {
                Swal.fire('Error', 'Failed to load holiday data', 'error');
            }
        });
    }

    function saveHoliday() {
        const formData = {
            holiday_name: $('#holiday_name').val(),
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            type: $('#type').val(),
            description: $('#description').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        const holidayID = $('#holidayID').val();
        const url = holidayID ? '/holidays/' + holidayID : '/holidays';
        const method = holidayID ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: formData,
            success: function(response) {
                Swal.fire('Success', response.message, 'success').then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.error || 'Failed to save holiday', 'error');
            }
        });
    }

    function deleteHoliday(holidayID) {
        Swal.fire({
            title: 'Delete Holiday?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/holidays/' + holidayID,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire('Deleted!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.error || 'Failed to delete holiday', 'error');
                    }
                });
            }
        });
    }

    // Bulk Holiday Functions
    function showBulkHolidayModal() {
        // Reset container to one row
        $('#bulkHolidaysContainer').html(`
            <div class="bulk-holiday-item mb-3 p-3 border rounded">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Holiday Name *</label>
                        <input type="text" class="form-control bulk-holiday-name" placeholder="e.g., Christmas Holiday" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Start Date *</label>
                        <input type="date" class="form-control bulk-start-date" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">End Date *</label>
                        <input type="date" class="form-control bulk-end-date" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Type *</label>
                        <select class="form-select bulk-holiday-type" required>
                            <option value="Public Holiday">Public Holiday</option>
                            <option value="School Holiday">School Holiday</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control bulk-description" rows="2" placeholder="Optional description"></textarea>
                    </div>
                </div>
            </div>
        `);
        new bootstrap.Modal(document.getElementById('bulkHolidayModal')).show();
    }

    function addBulkHolidayRow() {
        const newRow = `
            <div class="bulk-holiday-item mb-3 p-3 border rounded">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong class="text-primary">Holiday Entry</strong>
                    <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.bulk-holiday-item').remove()">
                        <i class="fa fa-trash"></i> Remove
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Holiday Name *</label>
                        <input type="text" class="form-control bulk-holiday-name" placeholder="e.g., Christmas Holiday" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Start Date *</label>
                        <input type="date" class="form-control bulk-start-date" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">End Date *</label>
                        <input type="date" class="form-control bulk-end-date" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Type *</label>
                        <select class="form-select bulk-holiday-type" required>
                            <option value="Public Holiday">Public Holiday</option>
                            <option value="School Holiday">School Holiday</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control bulk-description" rows="2" placeholder="Optional description"></textarea>
                    </div>
                </div>
            </div>
        `;
        $('#bulkHolidaysContainer').append(newRow);
    }

    function saveBulkHolidays() {
        const holidays = [];
        let isValid = true;

        $('.bulk-holiday-item').each(function() {
            const name = $(this).find('.bulk-holiday-name').val();
            const startDate = $(this).find('.bulk-start-date').val();
            const endDate = $(this).find('.bulk-end-date').val();
            const type = $(this).find('.bulk-holiday-type').val();
            const description = $(this).find('.bulk-description').val();

            if (!name || !startDate || !endDate || !type) {
                isValid = false;
                return false;
            }

            if (new Date(startDate) > new Date(endDate)) {
                isValid = false;
                Swal.fire('Error', 'Start date cannot be after end date', 'error');
                return false;
            }

            holidays.push({
                holiday_name: name,
                start_date: startDate,
                end_date: endDate,
                type: type,
                description: description || ''
            });
        });

        if (!isValid) {
            Swal.fire('Error', 'Please fill all required fields correctly', 'error');
            return;
        }

        if (holidays.length === 0) {
            Swal.fire('Error', 'Please add at least one holiday', 'error');
            return;
        }

        // Show loading
        Swal.fire({
            title: 'Saving Holidays...',
            html: `Saving ${holidays.length} holiday(s)`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '/holidays/bulk',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                holidays: holidays
            },
            success: function(response) {
                Swal.fire('Success', response.message, 'success').then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.error || 'Failed to save holidays', 'error');
            }
        });
    }

    // Event Functions
    function showAddEventModal() {
        $('#eventModalTitle').text('Add Event');
        $('#eventForm')[0].reset();
        $('#eventID').val('');
        $('#is_non_working_day').prop('checked', false);
        new bootstrap.Modal(document.getElementById('eventModal')).show();
    }

    function editEvent(eventID) {
        $.ajax({
            url: '/events/' + eventID,
            method: 'GET',
            success: function(response) {
                $('#eventModalTitle').text('Edit Event');
                $('#eventID').val(response.eventID);
                $('#event_name').val(response.event_name);
                $('#event_date').val(response.event_date);
                $('#start_time').val(response.start_time ? response.start_time.substring(0, 5) : '');
                $('#end_time').val(response.end_time ? response.end_time.substring(0, 5) : '');
                $('#event_type').val(response.type);
                $('#event_description').val(response.description);
                $('#is_non_working_day').prop('checked', response.is_non_working_day == 1);
                new bootstrap.Modal(document.getElementById('eventModal')).show();
            },
            error: function() {
                Swal.fire('Error', 'Failed to load event data', 'error');
            }
        });
    }

    function saveEvent() {
        const formData = {
            event_name: $('#event_name').val(),
            event_date: $('#event_date').val(),
            start_time: $('#start_time').val(),
            end_time: $('#end_time').val(),
            type: $('#event_type').val(),
            description: $('#event_description').val(),
            is_non_working_day: $('#is_non_working_day').is(':checked') ? 1 : 0,
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        const eventID = $('#eventID').val();
        const url = eventID ? '/events/' + eventID : '/events';
        const method = eventID ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: formData,
            success: function(response) {
                Swal.fire('Success', response.message, 'success').then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.error || 'Failed to save event', 'error');
            }
        });
    }

    function deleteEvent(eventID) {
        Swal.fire({
            title: 'Delete Event?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/events/' + eventID,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire('Deleted!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.error || 'Failed to delete event', 'error');
                    }
                });
            }
        });
    }
</script>

@include('includes.footer')

