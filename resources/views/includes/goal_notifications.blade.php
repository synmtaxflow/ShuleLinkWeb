@php
    $userID = Session::get('userID');
    $goalNotificationCount = 0;
    $goalNotifications = collect();

    // HOD Pending Reviews Logic
    $hodSubtaskNotifCount = 0;
    $hodSubtaskNotifs = collect();
    $navTeacherID = Session::get('teacherID');
    $navSchoolID  = Session::get('schoolID');

    if ($userID) {
        $goalNotificationsRaw = \App\Models\GoalNotification::where('user_id', $userID)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();
        $goalNotificationCount = $goalNotificationsRaw->count();
        $goalNotifications = $goalNotificationsRaw->take(10);
    }

    if ($navTeacherID && $navSchoolID) {
        $hodDepts = \App\Models\Department::where('schoolID', $navSchoolID)
            ->where('head_teacherID', $navTeacherID)
            ->pluck('departmentID');

        if ($hodDepts->count() > 0) {
            $hodTaskIds = \App\Models\GoalTask::whereIn('assigned_to_id', $hodDepts)
                ->where('assigned_to_type', 'Department')
                ->pluck('id');

            if ($hodTaskIds->count() > 0) {
                $memberTaskIds = \App\Models\GoalMemberTask::whereIn('parent_task_id', $hodTaskIds)
                    ->pluck('id');

                if ($memberTaskIds->count() > 0) {
                    $hodSubtaskNotifCount = \App\Models\GoalSubtask::whereIn('member_task_id', $memberTaskIds)
                        ->where('status', 'Submitted')
                        ->where('is_approved', false)
                        ->count();

                    if ($hodSubtaskNotifCount > 0) {
                        $hodSubtaskNotifs = \App\Models\GoalSubtask::with(['memberTask'])
                            ->whereIn('member_task_id', $memberTaskIds)
                            ->where('status', 'Submitted')
                            ->where('is_approved', false)
                            ->latest()
                            ->get();
                    }
                }
            }
        }
    }
    // Admin Pending Reviews (Direct Assignments)
    $adminSubtaskNotifCount = 0;
    $adminSubtaskNotifs = collect();
    if ($userID) {
        $adminSubtaskNotifCount = \App\Models\GoalSubtask::whereHas('directTask.goal', function($q) use ($userID) {
                $q->where('created_by', $userID);
            })
            ->where('status', 'Submitted')
            ->where('is_approved', false)
            ->count();

        if ($adminSubtaskNotifCount > 0) {
            $adminSubtaskNotifs = \App\Models\GoalSubtask::with(['directTask.goal', 'directTask.teacher', 'directTask.staff'])
                ->whereHas('directTask.goal', function($q) use ($userID) {
                    $q->where('created_by', $userID);
                })
                ->where('status', 'Submitted')
                ->where('is_approved', false)
                ->latest()
                ->get();

            foreach ($adminSubtaskNotifs as $notif) {
                $performer = null;
                if ($notif->directTask->assigned_to_type === 'Teacher') {
                    $performer = $notif->directTask->teacher;
                } else {
                    $performer = $notif->directTask->staff;
                }

                if ($performer) {
                    $notif->performer_name = ($performer->first_name ?? '') . ' ' . ($performer->last_name ?? '');
                    $notif->performer_image = $performer->image 
                        ? asset('userImages/' . $performer->image) 
                        : ($performer->gender == 'Female' ? asset('images/female.png') : asset('images/male.png'));
                } else {
                    $notif->performer_name = "Staff";
                    $notif->performer_image = asset('images/male.png');
                }
            }
        }
    }

    $totalGoalCount = $goalNotificationCount + $hodSubtaskNotifCount + $adminSubtaskNotifCount;
@endphp

<div class="dropdown for-notification">
    <button class="btn btn-secondary dropdown-toggle position-relative" type="button" id="goal-notifications" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background: transparent; border: none;">
        <i class="fa fa-flag-checkered" style="color: #666; font-size: 1.2rem;"></i>
        @if($totalGoalCount > 0)
            <span class="count bg-success" style="position: absolute; top: -5px; right: -5px; border-radius: 50%; padding: 2px 5px; font-size: 0.7rem; color: white;">{{ $totalGoalCount }}</span>
        @endif
    </button>
    <div class="dropdown-menu" aria-labelledby="goal-notifications" style="max-width: 400px; min-width: 300px; padding: 0; box-shadow: 0 5px 25px rgba(0,0,0,0.15); border-radius: 12px; border: none; margin-top: 10px;">
        <div class="px-3 py-2 d-flex justify-content-between align-items-center" style="background: #f8f9fa; border-bottom: 2px solid #28a745; border-radius: 12px 12px 0 0;">
            <span style="font-weight: 800; color: #28a745; font-size: 0.9rem;">Goal Management</span>
            @if($totalGoalCount > 0)
                <span class="badge badge-success px-2 py-1" style="font-size: 0.7rem;">{{ $totalGoalCount }} NEW</span>
            @endif
        </div>

        <div style="max-height: 400px; overflow-y: auto;">
            {{-- HOD Section --}}
            @if($hodSubtaskNotifCount > 0)
                <div style="background: #fffcf0; padding: 8px 15px; border-bottom: 1px solid #f0e68c; font-weight: 700; color: #856404; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fa fa-users mr-1"></i> Dept Reviews ({{ $hodSubtaskNotifCount }})
                </div>
                @foreach($hodSubtaskNotifs as $notif)
                    <a class="dropdown-item media" href="{{ route('hod.goals.assigned') }}" style="padding: 12px 15px; border-bottom: 1px solid #f8f8f8; transition: background 0.2s;">
                        <div class="media-body">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fa fa-send text-warning mr-2" style="font-size: 0.8rem;"></i>
                                <span style="font-size: 0.85rem; color: #333; font-weight: 700;">Subtask Submitted</span>
                            </div>
                            <p style="margin: 0; font-size: 0.8rem; color: #555; line-height: 1.3;">
                                "{{ Str::limit($notif->subtask_name, 40) }}" in task <b>{{ Str::limit($notif->memberTask->task_name ?? 'Task', 30) }}</b>
                            </p>
                            <small style="color: #999; font-size: 0.7rem;"><i class="fa fa-clock-o"></i> {{ $notif->updated_at->diffForHumans() }}</small>
                        </div>
                    </a>
                @endforeach
            @endif

            {{-- Admin Direct Review Section --}}
            @if($adminSubtaskNotifCount > 0)
                <div style="background: #f0f7ff; padding: 8px 15px; border-bottom: 1px solid #b3d7ff; font-weight: 700; color: #004085; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fa fa-shield mr-1"></i> Direct Reviews ({{ $adminSubtaskNotifCount }})
                </div>
                @foreach($adminSubtaskNotifs as $notif)
                    <a class="dropdown-item media" href="{{ route('admin.goals.show', $notif->directTask->goal_id) }}" style="padding: 12px 15px; border-bottom: 1px solid #f8f8f8; transition: background 0.2s;">
                        <img src="{{ $notif->performer_image }}" alt="User" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; margin-right: 12px; border: 1px solid #eee;">
                        <div class="media-body">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fa fa-paper-plane text-primary mr-1" style="font-size: 0.7rem;"></i>
                                <span style="font-size: 0.85rem; color: #333; font-weight: 700;">{{ $notif->performer_name }}</span>
                            </div>
                            <p style="margin: 0; font-size: 0.8rem; color: #555; line-height: 1.3;">
                                Amewasilisha <b>"{{ Str::limit($notif->subtask_name, 35) }}"</b>
                            </p>
                            <small style="color: #999; font-size: 0.7rem;"><i class="fa fa-clock-o"></i> {{ $notif->updated_at->diffForHumans() }}</small>
                        </div>
                    </a>
                @endforeach
            @endif

            {{-- General Section --}}
            @if($goalNotifications->isNotEmpty())
                @if($hodSubtaskNotifCount > 0)
                    <div style="padding: 8px 15px; border-bottom: 1px solid #eee; font-weight: 700; color: #666; font-size: 0.75rem; text-transform: uppercase;">
                        Earlier Notifications
                    </div>
                @endif
                @foreach($goalNotifications as $notification)
                    <a class="dropdown-item media goal-notification-item" href="{{ $notification->link ?? '#' }}" data-id="{{ $notification->id }}" style="padding: 12px 15px; border-bottom: 1px solid #f8f8f8; white-space: normal;">
                        <div class="media-body">
                            <p style="margin: 0; font-size: 0.85rem; color: #333; font-weight: 700;">{{ $notification->title }}</p>
                            <p style="margin: 0; font-size: 0.8rem; color: #666; line-height: 1.4;">{{ $notification->message }}</p>
                            <small style="color: #999; font-size: 0.7rem;"><i class="fa fa-clock-o"></i> {{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                    </a>
                @endforeach
            @endif

            @if($totalGoalCount == 0)
                <div class="text-center py-5">
                    <i class="fa fa-flag-o text-muted fa-3x mb-3" style="opacity: 0.1;"></i>
                    <p class="mb-0 text-muted">No pending tasks or updates</p>
                </div>
            @endif
        </div>

        @if($goalNotificationCount > 0)
            <div class="text-center py-2 border-top bg-light">
                <a class="small font-weight-bold" style="color: #28a745;" href="#" id="mark-all-goal-read">
                    <i class="fa fa-check-all"></i> Mark all as read
                </a>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery === 'undefined') return;
    const $ = jQuery;

    $('.goal-notification-item').on('click', function(e) {
        const id = $(this).data('id');
        $.post('{{ url("goals/notifications/read") }}/' + id, {
            _token: '{{ csrf_token() }}'
        });
    });

    $('#mark-all-goal-read').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        // Here we could add a read-all route if needed, for now just individual on click is enough or we can add it to controller
        $.post('{{ url("goals/notifications/read-all") }}', {
            _token: '{{ csrf_token() }}'
        }, function(response) {
            if(response.success) {
                location.reload();
            }
        });
    });
});
</script>
