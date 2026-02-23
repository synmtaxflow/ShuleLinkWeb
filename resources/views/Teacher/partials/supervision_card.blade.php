<div class="col-md-6 col-lg-4 mb-3">
    <div class="session-card widget-card {{ $isActive ? 'active' : '' }}">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <span class="time-badge">
                    <i class="bi bi-clock"></i> 
                    {{ \Carbon\Carbon::parse($assignment->start_time)->format('h:i A') }} - 
                    {{ \Carbon\Carbon::parse($assignment->end_time)->format('h:i A') }}
                </span>
            </div>
            @if($isActive)
                <span class="badge badge-success" style="background-color: #28a745 !important;">
                    <i class="bi bi-circle-fill"></i> Active Today
                </span>
            @elseif(isset($assignment->is_past) && $assignment->is_past)
                <span class="badge badge-secondary" style="background-color: #6c757d !important;">
                    <i class="bi bi-clock-history"></i> Past Session
                </span>
            @else
                <span class="badge badge-info" style="background-color: #17a2b8 !important;">
                    <i class="bi bi-calendar2-event"></i> Upcoming Session
                </span>
            @endif
        </div>
        
        <div class="mb-2">
            <span class="badge {{ $assignment->exam_category === 'test' ? 'badge-warning' : 'badge-info' }}">
                {{ $assignment->exam_category === 'test' ? 'Weekly/Monthly Test' : 'Standard Exam' }}
            </span>
        </div>

        <h6 class="mb-2" style="font-weight: bold;">
            <i class="bi bi-book text-primary-custom"></i> 
            {{ $assignment->subject_name }}
        </h6>
        
        <p class="mb-1 text-muted small">
            <i class="bi bi-award"></i> {{ $assignment->exam_name }}
        </p>

        <p class="mb-1 text-muted small">
            <i class="bi bi-building"></i> 
            <strong>Hall:</strong> {{ $assignment->hall_name ?: ($assignment->exam_category === 'test' ? $assignment->class_name : 'N/A') }}
        </p>

        <p class="mb-2 text-muted small">
            <i class="bi bi-people"></i> 
            <strong>Group:</strong> {{ $assignment->class_name }} ({{ $assignment->students_count }} Students)
        </p>
        
        @if($assignment->exam_category === 'test' && isset($assignment->week_range))
        <div class="mb-2 p-2 bg-light border-left border-success">
            <small class="text-muted d-block">Assigned Week:</small>
            <strong class="text-success small"><i class="bi bi-calendar-range"></i> {{ $assignment->week_range }}</strong>
        </div>
        @endif

        <div class="mb-3">
            <small class="text-muted"><i class="bi bi-calendar3"></i> Date: {{ date('D, M d, Y', strtotime($assignment->exam_date)) }}</small>
        </div>

        <div class="session-actions">
            @php
                $viewStudentsUrl = route('supervise_exam.view_students', [
                    'hall_id' => $assignment->exam_hallID,
                    'subject_id' => $assignment->subjectID,
                    'timetable_id' => $assignment->exam_timetableID,
                    'exam_category' => $assignment->exam_category,
                    'examID' => $assignment->examID,
                    'classID' => $assignment->classID,
                    'scope' => $assignment->scope ?? null,
                    'date' => $assignment->exam_date
                ]);

                $takeAttendanceUrl = route('supervise_exam.take_attendance', [
                    'hall_id' => $assignment->exam_hallID,
                    'subject_id' => $assignment->subjectID,
                    'timetable_id' => $assignment->exam_timetableID,
                    'examID' => $assignment->examID,
                    'date' => $assignment->exam_date,
                    'exam_category' => $assignment->exam_category,
                    'classID' => $assignment->classID,
                    'scope' => $assignment->scope ?? null
                ]);
            @endphp

            @if($assignment->exam_category !== 'test')
                <a href="{{ $viewStudentsUrl }}" class="btn btn-session-action btn-sm">
                    <i class="bi bi-people"></i> View Students
                </a>

                @if($isActive && $assignment->exam_date)
                    <a href="{{ $takeAttendanceUrl }}" target="_blank" class="btn btn-session-action btn-sm" style="background-color: #940000 !important; color: white !important;">
                        <i class="bi bi-clipboard-check"></i> Take Attendance
                    </a>
                @endif
            @else
                {{-- For Daily/Weekly Tests --}}
                <a href="{{ $viewStudentsUrl }}" class="btn btn-session-action btn-sm">
                    <i class="bi bi-people"></i> View Test Students
                </a>
                @if($isActive)
                    <a href="{{ $takeAttendanceUrl }}" target="_blank" class="btn btn-session-action btn-sm" style="background-color: #940000 !important; color: white !important;">
                        <i class="bi bi-clipboard-check"></i> Test Attendance
                    </a>
                @endif
            @endif
        </div>
    </div>
</div>
