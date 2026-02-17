@php 
    $groupedDuties = $duties->groupBy(function($item) {
        return $item->start_date . '_' . $item->end_date;
    });
    $weekCounter = 1;
    $today = \Carbon\Carbon::now();
@endphp

@foreach($groupedDuties as $key => $weekGroup)
    @php
        $startDate = \Carbon\Carbon::parse($weekGroup[0]->start_date);
        $endDate = \Carbon\Carbon::parse($weekGroup[0]->end_date);
        $assignedTeacherIds = $weekGroup->pluck('teacherID')->toArray();
        $isActive = $today->between($startDate, $endDate);
    @endphp
    <tr id="duty-row-{{ $startDate->format('Y-m-d') }}" class="{{ $isActive ? 'table-primary active-week' : '' }} expandable-row" data-toggle="collapse" data-target="#details-{{ $startDate->format('Y-m-d') }}" style="cursor: pointer;">
        <td>
            <i class="fa fa-chevron-right mr-2 transition-icon"></i>
            @if($isActive)
                <div class="active-indicator" title="Current Active Week">
                    <i class="fa fa-clock-o"></i> Active
                </div>
            @endif
            {{ $weekCounter++ }}
        </td>
        <td>
            @foreach($weekGroup as $duty)
                <span class="badge badge-info">{{ $duty->teacher ? $duty->teacher->first_name . ' ' . $duty->teacher->last_name : 'N/A' }}</span>
            @endforeach
        </td>
        <td>
            {{ $startDate->format('d M') }} - {{ $endDate->format('d M Y') }}
        </td>
        <td>{{ $weekGroup[0]->term ? $weekGroup[0]->term->term_name : 'N/A' }}</td>
        <td>
            <div class="btn-group">
                <button class="btn btn-sm btn-info edit-duty" 
                        data-start="{{ $startDate->format('Y-m-d') }}" 
                        data-teachers="{{ json_encode($assignedTeacherIds) }}">
                    <i class="fa fa-edit"></i> Edit
                </button>
                <button class="btn btn-sm btn-danger delete-duty" 
                        data-start="{{ $startDate->format('Y-m-d') }}" 
                        data-end="{{ $endDate->format('Y-m-d') }}">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        </td>
    </tr>
    <tr id="details-{{ $startDate->format('Y-m-d') }}" class="collapse bg-light">
        <td colspan="5" class="p-0">
            <div class="p-3">
                <h6 class="mb-3"><i class="fa fa-calendar"></i> Daily Reports for the Week</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover bg-white mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Status</th>
                                <th>Teacher</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $current = $startDate->copy();
                            @endphp
                            @while($current->lte($endDate))
                                @php
                                    $dateStr = $current->format('Y-m-d');
                                    // Multiple teachers might have reports if it's a shared week, 
                                    // but usually they share one report or have separate.
                                    // Given the logic, it seems they might share or one fills it.
                                    $dayReports = $reports->get($dateStr) ?? collect();
                                @endphp
                                <tr>
                                    <td>{{ $current->format('d/m/Y') }}</td>
                                    <td>{{ $current->format('l') }}</td>
                                    <td>
                                        @if($dayReports->isEmpty())
                                            <span class="badge badge-secondary">Pending</span>
                                        @else
                                            @foreach($dayReports as $rep)
                                                @php
                                                    $statusClass = 'badge-secondary';
                                                    if($rep->status === 'Sent') $statusClass = 'badge-warning';
                                                    if($rep->status === 'Approved') $statusClass = 'badge-success';
                                                    if($rep->status === 'Draft') $statusClass = 'badge-info';
                                                @endphp
                                                <span class="badge {{ $statusClass }}">{{ $rep->status }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        @if($dayReports->isEmpty())
                                            <span class="text-muted small">Not submitted</span>
                                        @else
                                            @foreach($dayReports as $rep)
                                                <span class="small">{{ $rep->teacher ? $rep->teacher->first_name . ' ' . $rep->teacher->last_name : 'N/A' }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        @foreach($dayReports as $rep)
                                            @if($rep->status === 'Sent')
                                                <button class="btn btn-xs btn-primary view-sign-report" 
                                                        data-id="{{ $rep->reportID }}" 
                                                        data-date="{{ $dateStr }}">
                                                    <i class="fa fa-pencil"></i> View & Sign
                                                </button>
                                            @elseif($rep->status === 'Approved')
                                                <button class="btn btn-xs btn-success view-report-admin" 
                                                        data-id="{{ $rep->reportID }}" 
                                                        data-date="{{ $dateStr }}">
                                                    <i class="fa fa-eye"></i> View
                                                </button>
                                                <a href="{{ route('teacher.duty_book.export_report', ['date' => $dateStr, 'teacherID' => $rep->teacherID]) }}" class="btn btn-xs btn-danger">
                                                    <i class="fa fa-file-pdf-o"></i> PDF
                                                </a>
                                            @endif
                                        @endforeach
                                    </td>
                                </tr>
                                @php $current->addDay(); @endphp
                            @endwhile
                        </tbody>
                    </table>
                </div>
            </div>
        </td>
    </tr>
@endforeach

@if($groupedDuties->isEmpty())
    <tr>
        <td colspan="5" class="text-center py-5">
            <i class="fa fa-info-circle fa-2x text-muted mb-2"></i>
            <p>No duties assigned for the selected period.</p>
        </td>
    </tr>
@endif
