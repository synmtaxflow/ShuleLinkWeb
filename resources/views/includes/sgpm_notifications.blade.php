@php
    $userID = Session::get('userID');
    $sgpmNotificationCount = 0;
    $sgpmNotifications = collect();
    if ($userID) {
        $sgpmNotificationsRaw = \App\Models\SgpmNotification::where('user_id', $userID)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();
        $sgpmNotificationCount = $sgpmNotificationsRaw->count();
        $sgpmNotifications = $sgpmNotificationsRaw->take(5);
    }
@endphp

<div class="dropdown for-notification">
    <button class="btn btn-secondary dropdown-toggle position-relative" type="button" id="sgpm-notifications" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background: transparent; border: none;">
        <i class="fa fa-bullseye" style="color: #666; font-size: 1.2rem;"></i>
        @if($sgpmNotificationCount > 0)
            <span class="count bg-danger" style="position: absolute; top: -5px; right: -5px; border-radius: 50%; padding: 2px 5px; font-size: 0.7rem; color: white;">{{ $sgpmNotificationCount }}</span>
        @endif
    </button>
    <div class="dropdown-menu" aria-labelledby="sgpm-notifications" style="max-width: 350px; min-width: 280px; padding: 0; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-radius: 10px; border: none;">
        <p class="px-3 py-2 mb-0" style="font-weight: bold; border-bottom: 1px solid #f0f0f0; color: #940000;">Strategic Management</p>
        <div style="max-height: 300px; overflow-y: auto;">
            @if($sgpmNotifications->isEmpty())
                <p class="px-3 py-3 mb-0 text-center text-muted">No new notifications</p>
            @else
                @foreach($sgpmNotifications as $notification)
                    <a class="dropdown-item media sgpm-notification-item" href="{{ $notification->link ?? '#' }}" data-id="{{ $notification->notificationID }}" style="padding: 10px 15px; border-bottom: 1px solid #f8f8f8; white-space: normal;">
                        <div class="media-body">
                            <p style="margin: 0; font-size: 0.85rem; color: #333; font-weight: 600;">{{ $notification->title }}</p>
                            <p style="margin: 0; font-size: 0.8rem; color: #666;">{{ $notification->message }}</p>
                            <small style="color: #888;">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                    </a>
                @endforeach
            @endif
        </div>
        @if($sgpmNotificationCount > 0)
            <div class="text-center py-2 border-top">
                <a class="small text-primary" href="#" id="mark-all-sgpm-read">Mark all as read</a>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery === 'undefined') return; // Exit if jQuery is not loaded
    const $ = jQuery;

    $('.sgpm-notification-item').on('click', function(e) {
        const id = $(this).data('id');
        $.post('{{ route("sgpm.notifications.read") }}', {
            _token: '{{ csrf_token() }}',
            notificationID: id
        });
    });

    $('#mark-all-sgpm-read').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $.post('{{ route("sgpm.notifications.read_all") }}', {
            _token: '{{ csrf_token() }}'
        }, function(response) {
            if(response.success) {
                location.reload();
            }
        });
    });
});
</script>
