@if($user_type == 'Admin')
@include('includes.Admin_nav')
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
    }

    .view-only-badge {
        background-color: #6c757d;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        display: inline-block;
        margin-bottom: 20px;
    }
</style>

<div class="container-fluid calendar-container">
    <div class="calendar-header">
        <h2>
            <i class="fa fa-calendar"></i>
            School Calendar - {{ $currentYear }}
        </h2>
        <span class="view-only-badge">
            <i class="fa fa-eye"></i> View Only
        </span>
    </div>

    <!-- Statistics Summary -->
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
            <div class="stat-label">Non-Working Events</div>
            <div class="stat-number">{{ $stats['non_working_event_days'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Teacher Sessions/Year</div>
            <div class="stat-number">{{ $stats['total_sessions'] }}</div>
        </div>
    </div>

    <!-- All Holidays Summary Widget -->
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
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">
                                            <i class="fa fa-info-circle"></i> No school holidays defined
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

    <!-- Year Selector -->
    <div class="year-selector">
        <label for="yearSelect" class="form-label mb-0">Year:</label>
        <select class="form-select" id="yearSelect" style="width: 150px;">
            @for($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </div>

    <!-- Calendar Grid (12 Months) - View Only -->
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
                                <span class="holiday-badge">{{ $holiday->holiday_name }}</span>
                                <small class="text-muted">
                                    ({{ \Carbon\Carbon::parse($holiday->start_date)->format('M d') }} - 
                                     {{ \Carbon\Carbon::parse($holiday->end_date)->format('M d') }})
                                </small>
                                @if($holiday->description)
                                    <br><small class="text-muted">{{ $holiday->description }}</small>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted small">No school holidays</p>
                    @endif
                    
                    @if($monthAutoHolidays->count() > 0)
                        <strong class="text-success mt-2 d-block">Public Holidays:</strong>
                        @foreach($monthAutoHolidays as $autoHoliday)
                            <div class="list-item">
                                <span class="badge bg-success">{{ $autoHoliday['name'] }}</span>
                                <small class="text-muted">
                                    ({{ $autoHoliday['date']->format('M d') }})
                                </small>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="events-list mt-2">
                    <strong class="text-info">Events:</strong>
                    @if($monthEvents->count() > 0)
                        @foreach($monthEvents as $event)
                            <div class="list-item">
                                <span class="event-badge">{{ $event->event_name }}</span>
                                <small class="text-muted">
                                    ({{ \Carbon\Carbon::parse($event->event_date)->format('M d') }})
                                </small>
                                @if($event->start_time)
                                    <br><small class="text-muted">
                                        Time: {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}
                                        @if($event->end_time)
                                            - {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}
                                        @endif
                                    </small>
                                @endif
                                @if($event->description)
                                    <br><small class="text-muted">{{ $event->description }}</small>
                                @endif
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Year selector change
    $('#yearSelect').on('change', function() {
        const year = $(this).val();
        window.location.href = '{{ route("teacher.calendar") }}?year=' + year;
    });
</script>

@include('includes.footer')

