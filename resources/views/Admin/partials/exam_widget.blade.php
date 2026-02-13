@php
    // Check if this is a Weekly Test or Monthly Test
    $isWeeklyTest = ($exam->exam_name === 'Weekly Test' || $exam->start_date === 'every_week' || $exam->end_date === 'every_week');
    $isMonthlyTest = ($exam->exam_name === 'Monthly Test' || $exam->start_date === 'every_month' || $exam->end_date === 'every_month');
    
    // Calculate days remaining (only for non-weekly/monthly tests)
    $daysUntilStart = null;
    $daysUntilEnd = null;
    if (!$isWeeklyTest && !$isMonthlyTest) {
        try {
    $today = now()->startOfDay();
    $startDate = \Carbon\Carbon::parse($exam->start_date)->startOfDay();
    $endDate = \Carbon\Carbon::parse($exam->end_date)->startOfDay();
    $daysUntilStart = $today->diffInDays($startDate, false);
    $daysUntilEnd = $today->diffInDays($endDate, false);
        } catch (\Exception $e) {
            // If date parsing fails, treat as weekly/monthly
            $daysUntilStart = null;
            $daysUntilEnd = null;
        }
    }
    
    // Get student counts
    $expectedStudents = $exam->expected_students ?? 0;
    $studentsWithMarks = $exam->students_with_marks ?? 0;
    $studentsWithoutMarks = $exam->students_without_marks ?? 0;
    
    // Determine status message
    $statusMessage = '';
    $statusClass = 'info';
    
    if ($isWeeklyTest) {
        // For Weekly Test, show "every week"
        $statusMessage = 'Every week';
        $statusClass = 'info';
    } elseif ($isMonthlyTest) {
        // For Monthly Test, show "every month in a term"
        $statusMessage = 'Every month in a term';
        $statusClass = 'info';
    } elseif (($exam->status ?? '') == 'scheduled' || ($exam->status ?? '') == 'wait_approval') {
        if ($daysUntilStart !== null && $daysUntilStart >= 0) {
            $statusMessage = $daysUntilStart == 0 ? 'Starts today' : ($daysUntilStart == 1 ? '1 day to start' : $daysUntilStart . ' days to start');
        }
    } elseif (($exam->status ?? '') == 'ongoing') {
        if ($daysUntilEnd !== null && $daysUntilEnd >= 0) {
            $statusMessage = $daysUntilEnd == 0 ? 'Ends today' : ($daysUntilEnd == 1 ? '1 day remaining' : $daysUntilEnd . ' days remaining');
            $statusClass = 'warning';
        }
    } elseif (($exam->status ?? '') == 'awaiting_results' || ($exam->status ?? '') == 'results_available') {
        $statusMessage = 'Completed';
        $statusClass = 'success';
    }
@endphp

<div class="card exam-widget-card h-100">
    <div class="card-body p-3">
        <!-- Header -->
        <div class="mb-3">
            <h6 class="mb-1 exam-widget-title">{{ $exam->exam_name }}</h6>
            <small class="exam-widget-meta">
                @if($exam->term)
                    {{ ucfirst(str_replace('_', ' ', $exam->term)) }} • 
                @endif
                {{ $exam->year }}
                
                @if(isset($exam->test_end_date_range) && $exam->test_end_date_range)
                    <br>
                    <div class="mt-2">
                        <button class="btn btn-link p-0 d-flex align-items-center cursor-pointer text-primary-custom text-decoration-none w-100 collapsed test-breakdown-toggle" 
                                style="font-size: 0.85rem; font-weight: 600;" 
                                type="button"
                                data-target="#breakdown_{{ $exam->examID }}"
                                aria-expanded="false"
                                aria-controls="breakdown_{{ $exam->examID }}">
                            <i class="bi bi-calendar-check me-1"></i> 
                            Final Week: {{ $exam->test_end_date_range }}
                            <i class="bi bi-chevron-down ms-auto transition-transform" style="font-size: 0.7rem;"></i>
                        </button>
                        
                        @if(isset($exam->test_breakdown) && count($exam->test_breakdown) > 0)
                        <div class="mt-2" id="breakdown_{{ $exam->examID }}" style="display: none;">
                            <div class="p-2 rounded border" style="background-color: #fafafa; border-color: #f1d7d7 !important;">
                                <ul class="list-unstyled mb-0" style="font-size: 0.75rem; color: #444;">
                                    @foreach($exam->test_breakdown as $item)
                                    <li class="mb-1 d-flex justify-content-between align-items-start border-bottom pb-1" style="border-bottom-style: dotted !important;">
                                        <span class="fw-bold text-dark" style="max-width: 60%;">{{ $item['name'] }}</span>
                                        <div class="text-end">
                                            <div class="fw-bold">Week {{ $item['week'] }}</div>
                                            <div class="text-muted" style="font-size: 0.65rem;">{{ $item['date_range'] }}</div>
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endif
                    </div>
                @elseif(isset($exam->total_weeks_count) && $exam->total_weeks_count > 0)
                    <br>
                    <span class="text-info mt-1 d-inline-block" style="font-size: 0.8rem;">
                        <i class="bi bi-calendar-range"></i> 
                        Total Weeks: {{ $exam->total_weeks_count }}
                    </span>
                @endif
            </small>
        </div>

        <!-- Student Statistics -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">Expected Students:</span>
                <strong class="text-dark">{{ $expectedStudents }}</strong>
            </div>
        </div>

        <!-- Status Badge -->
        @if($statusMessage)
        <div class="mb-2">
            <span class="badge exam-widget-status w-100 py-2">
                <i class="bi bi-{{ $statusClass == 'warning' ? 'hourglass-split' : ($statusClass == 'success' ? 'check-circle' : 'calendar-event') }}"></i> 
                {{ $statusMessage }}
            </span>
        </div>
        @endif

        <!-- Approval Status -->
        @if(($exam->approval_status ?? 'Pending') == 'Pending')
        <div class="mb-2">
            <span class="badge bg-warning text-dark w-100 py-2">
                <i class="bi bi-clock-history"></i> Pending Approval
            </span>
        </div>
        @elseif(($exam->approval_status ?? 'Pending') == 'Rejected')
        <div class="mb-2">
            <span class="badge bg-danger text-white w-100 py-2">
                <i class="bi bi-x-circle"></i> Rejected
            </span>
        </div>
        @endif

        <!-- Action Icons -->
        <div class="mt-3 pt-2 border-top">
            <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap">
                <!-- View More - Allowed for read_only, create, update, delete permissions -->
                @php
                    $canView = ($user_type ?? '') == 'Admin' || 
                               ($teacherPermissions ?? collect())->contains('examination_read_only') ||
                               ($teacherPermissions ?? collect())->contains('examination_create') ||
                               ($teacherPermissions ?? collect())->contains('examination_update') ||
                               ($teacherPermissions ?? collect())->contains('examination_delete');
                @endphp
                @if($canView)
                <button class="btn exam-widget-action" onclick="viewExamMore({{ $exam->examID }});" title="View More">
                    <i class="bi bi-eye"></i> View More
                </button>
                @endif
                
                <!-- View Exam Papers -->
                @if(($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('view_exam_papers'))
                <button class="btn exam-widget-action view-exam-papers-btn"
                        data-exam-id="{{ $exam->examID }}"
                        data-exam-name="{{ $exam->exam_name }}"
                        title="View Exam Papers">
                    <i class="bi bi-file-earmark-text"></i> Exam Papers
                    <span class="badge badge-danger ml-1 d-none exam-paper-count" data-exam-id="{{ $exam->examID }}"></span>
                </button>
                @endif
                
                <!-- View Exam Halls -->
                <button class="btn exam-widget-action view-exam-halls-btn"
                        data-exam-id="{{ $exam->examID }}"
                        data-exam-name="{{ $exam->exam_name }}"
                        title="View Exam Halls">
                    <i class="bi bi-building"></i> Exam Halls
                </button>
                
                <!-- Edit -->
                @php
                    $canUpdate = ($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('examination_update');
                @endphp
                @if($canUpdate)
                <button class="btn exam-widget-action" onclick="editExam({{ $exam->examID }});" title="Edit">
                    <i class="bi bi-pencil"></i> Edit
                </button>
                @endif
                
                <!-- Allow Enter Result - Update action -->
                @if($canUpdate)
                <button class="btn exam-widget-action toggle-enter-result-btn" 
                        data-exam-id="{{ $exam->examID }}"
                        data-current-value="{{ $exam->enter_result ? 'true' : 'false' }}"
                        title="{{ $exam->enter_result ? 'Disallow Enter Result' : 'Allow Enter Result' }}">
                    <i class="bi {{ $exam->enter_result ? 'bi-check-circle' : 'bi-x-circle' }}"></i> Enter Result
                </button>
                @endif
                
                <!-- Publish Result - Update action -->
                @if($canUpdate)
                <button class="btn exam-widget-action toggle-publish-result-btn" 
                        data-exam-id="{{ $exam->examID }}"
                        data-current-value="{{ $exam->publish_result ? 'true' : 'false' }}"
                        title="{{ $exam->publish_result ? 'Unpublish Result' : 'Publish Result' }}">
                    <i class="bi {{ $exam->publish_result ? 'bi-eye' : 'bi-eye-slash' }}"></i> Publish
                </button>
                @endif
                
                <!-- Auto Shift Students - Update action -->
                @if($canUpdate)
                    @if(($exam->student_shifting_status ?? 'none') !== 'none')
                    <button class="btn exam-widget-action auto-shift-students-btn" 
                            data-exam-id="{{ $exam->examID }}"
                            data-shifting-status="{{ $exam->student_shifting_status }}"
                            title="Auto Shift Students ({{ ucfirst($exam->student_shifting_status) }})">
                        <i class="bi bi-arrow-right-circle"></i> Auto Shift
                    </button>
                    @endif
                @endif
                
                <!-- Unshift Students - Update action -->
                @if($canUpdate)
                    @if(($exam->student_shifting_status ?? 'none') !== 'none')
                    <button class="btn exam-widget-action unshift-students-btn" 
                            data-exam-id="{{ $exam->examID }}"
                            title="Unshift Students (Revert to Previous Classes)">
                        <i class="bi bi-arrow-left-circle"></i> Unshift
                    </button>
                    @endif
                @endif
                
                <!-- Toggle Upload Paper - Update action -->
                @if($canUpdate)
                <button class="btn exam-widget-action toggle-upload-paper-btn" 
                        data-exam-id="{{ $exam->examID }}"
                        data-current-value="{{ ($exam->upload_paper ?? true) ? 'true' : 'false' }}"
                        title="{{ ($exam->upload_paper ?? true) ? 'Disallow Upload Paper' : 'Allow Upload Paper' }}">
                    <i class="bi {{ ($exam->upload_paper ?? true) ? 'bi-file-earmark-arrow-up' : 'bi-file-earmark-x' }}"></i> Upload Paper
                </button>
                @endif
                
                <!-- Delete -->
                @php
                    $canDelete = ($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('examination_delete');
                @endphp
                @if($canDelete)
                <button class="btn exam-widget-action" onclick="deleteExam({{ $exam->examID }}, '{{ addslashes($exam->exam_name) }}');" title="Delete">
                    <i class="bi bi-trash"></i> Delete
                </button>
                @endif
            </div>
        </div>

        <!-- Approval Buttons - Update action -->
        @if(($exam->approval_status ?? 'Pending') == 'Pending')
        <div class="d-grid gap-2 mt-2">
            <div class="btn-group">
                {{-- Admin or users with examination_update permission can approve/reject --}}
                @if($canUpdate)
                <button class="btn btn-sm btn-success approve-exam-btn" data-exam-id="{{ $exam->examID }}" data-exam-name="{{ $exam->exam_name }}">
                    <i class="bi bi-check-circle"></i> Approve
                </button>
                <button class="btn btn-sm btn-danger reject-exam-btn" data-exam-id="{{ $exam->examID }}" data-exam-name="{{ $exam->exam_name }}">
                    <i class="bi bi-x-circle"></i> Reject
                </button>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
