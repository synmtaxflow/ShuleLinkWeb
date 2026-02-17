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
    <tr id="duty-row-{{ $startDate->format('Y-m-d') }}" class="{{ $isActive ? 'table-primary active-week' : '' }}">
        <td>
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
@endforeach

@if($groupedDuties->isEmpty())
    <tr>
        <td colspan="5" class="text-center py-5">
            <i class="fa fa-info-circle fa-2x text-muted mb-2"></i>
            <p>No duties assigned for the selected period.</p>
        </td>
    </tr>
@endif
