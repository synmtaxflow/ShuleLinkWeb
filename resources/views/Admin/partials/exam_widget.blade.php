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

<div class="card border shadow-sm h-100">
    <div class="card-body p-3">
        <!-- Header -->
        <div class="mb-3">
            <h6 class="mb-1 fw-bold text-dark">{{ $exam->exam_name }}</h6>
            <small class="text-muted">
                @if($exam->term)
                    {{ ucfirst(str_replace('_', ' ', $exam->term)) }} • 
                @endif
                {{ $exam->year }}
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
            <span class="badge bg-{{ $statusClass }} text-white w-100 py-2">
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
                <button class="btn btn-sm p-2" onclick="viewExamMore({{ $exam->examID }});" title="View More" 
                        style="min-width: 45px; height: 45px; background-color: #e3f2fd; border: 1px solid #2196f3; color: #1976d2; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-eye" style="font-size: 1.3rem;"></i>
                </button>
                @endif
                
                <!-- View Exam Papers -->
                @if(($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('view_exam_papers'))
                <button class="btn btn-sm p-2 view-exam-papers-btn" 
                        data-exam-id="{{ $exam->examID }}"
                        data-exam-name="{{ $exam->exam_name }}"
                        title="View Exam Papers" 
                        style="min-width: 45px; height: 45px; background-color: #f3e5f5; border: 1px solid #9c27b0; color: #7b1fa2; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-file-earmark-text" style="font-size: 1.3rem;"></i>
                </button>
                @endif
                
                <!-- View Exam Halls -->
                <button class="btn btn-sm p-2 view-exam-halls-btn" 
                        data-exam-id="{{ $exam->examID }}"
                        data-exam-name="{{ $exam->exam_name }}"
                        title="View Exam Halls" 
                        style="min-width: 45px; height: 45px; background-color: #fff3e0; border: 1px solid #ff9800; color: #e65100; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-building" style="font-size: 1.3rem;"></i>
                </button>
                
                <!-- Edit -->
                @php
                    $canUpdate = ($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('examination_update');
                @endphp
                @if($canUpdate)
                <button class="btn btn-sm p-2" onclick="editExam({{ $exam->examID }});" title="Edit" 
                        style="min-width: 45px; height: 45px; background-color: #e0f2f1; border: 1px solid #009688; color: #00695c; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-pencil" style="font-size: 1.3rem;"></i>
                </button>
                @endif
                
                <!-- Allow Enter Result - Update action -->
                @if($canUpdate)
                <button class="btn btn-sm p-2 toggle-enter-result-btn" 
                        data-exam-id="{{ $exam->examID }}"
                        data-current-value="{{ $exam->enter_result ? 'true' : 'false' }}"
                        title="{{ $exam->enter_result ? 'Disallow Enter Result' : 'Allow Enter Result' }}"
                        style="min-width: 45px; height: 45px; background-color: {{ $exam->enter_result ? '#e8f5e9' : '#f5f5f5' }}; border: 1px solid {{ $exam->enter_result ? '#4caf50' : '#9e9e9e' }}; color: {{ $exam->enter_result ? '#2e7d32' : '#616161' }}; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi {{ $exam->enter_result ? 'bi-check-circle' : 'bi-x-circle' }}" style="font-size: 1.3rem;"></i>
                </button>
                @endif
                
                <!-- Publish Result - Update action -->
                @if($canUpdate)
                <button class="btn btn-sm p-2 toggle-publish-result-btn" 
                        data-exam-id="{{ $exam->examID }}"
                        data-current-value="{{ $exam->publish_result ? 'true' : 'false' }}"
                        title="{{ $exam->publish_result ? 'Unpublish Result' : 'Publish Result' }}"
                        style="min-width: 45px; height: 45px; background-color: {{ $exam->publish_result ? '#fff3e0' : '#f5f5f5' }}; border: 1px solid {{ $exam->publish_result ? '#ff9800' : '#9e9e9e' }}; color: {{ $exam->publish_result ? '#e65100' : '#616161' }}; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi {{ $exam->publish_result ? 'bi-eye' : 'bi-eye-slash' }}" style="font-size: 1.3rem;"></i>
                </button>
                @endif
                
                <!-- Auto Shift Students - Update action -->
                @if($canUpdate)
                    @if(($exam->student_shifting_status ?? 'none') !== 'none')
                    <button class="btn btn-sm p-2 auto-shift-students-btn" 
                            data-exam-id="{{ $exam->examID }}"
                            data-shifting-status="{{ $exam->student_shifting_status }}"
                            title="Auto Shift Students ({{ ucfirst($exam->student_shifting_status) }})"
                            style="min-width: 45px; height: 45px; background-color: #e8f5e9; border: 1px solid #4caf50; color: #2e7d32; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-arrow-right-circle" style="font-size: 1.3rem;"></i>
                    </button>
                    @endif
                @endif
                
                <!-- Unshift Students - Update action -->
                @if($canUpdate)
                    @if(($exam->student_shifting_status ?? 'none') !== 'none')
                    <button class="btn btn-sm p-2 unshift-students-btn" 
                            data-exam-id="{{ $exam->examID }}"
                            title="Unshift Students (Revert to Previous Classes)"
                            style="min-width: 45px; height: 45px; background-color: #fff3e0; border: 1px solid #ff9800; color: #e65100; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-arrow-left-circle" style="font-size: 1.3rem;"></i>
                    </button>
                    @endif
                @endif
                
                <!-- Toggle Upload Paper - Update action -->
                @if($canUpdate)
                <button class="btn btn-sm p-2 toggle-upload-paper-btn" 
                        data-exam-id="{{ $exam->examID }}"
                        data-current-value="{{ ($exam->upload_paper ?? true) ? 'true' : 'false' }}"
                        title="{{ ($exam->upload_paper ?? true) ? 'Disallow Upload Paper' : 'Allow Upload Paper' }}"
                        style="min-width: 45px; height: 45px; background-color: {{ ($exam->upload_paper ?? true) ? '#e1f5fe' : '#f5f5f5' }}; border: 1px solid {{ ($exam->upload_paper ?? true) ? '#0288d1' : '#9e9e9e' }}; color: {{ ($exam->upload_paper ?? true) ? '#01579b' : '#616161' }}; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi {{ ($exam->upload_paper ?? true) ? 'bi-file-earmark-arrow-up' : 'bi-file-earmark-x' }}" style="font-size: 1.3rem;"></i>
                </button>
                @endif
                
                <!-- Delete -->
                @php
                    $canDelete = ($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('examination_delete');
                @endphp
                @if($canDelete)
                <button class="btn btn-sm p-2" onclick="deleteExam({{ $exam->examID }}, '{{ addslashes($exam->exam_name) }}');" title="Delete" 
                        style="min-width: 45px; height: 45px; background-color: #ffebee; border: 1px solid #f44336; color: #c62828; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-trash" style="font-size: 1.3rem;"></i>
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
