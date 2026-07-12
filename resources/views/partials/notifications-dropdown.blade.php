<li class="header-notification">
    <a class="d-block head-icon position-relative" href="#" role="button"
       data-bs-toggle="dropdown" aria-expanded="false">
        <i class="iconoir-bell"></i>
        @if ($notifications->where('read_at', null)->isNotEmpty())
            <span class="position-absolute top-space-5 start-100 translate-middle badge rounded-pill bg-danger badge-notification">
                {{ $notifications->where('read_at', null)->count() }}
            </span>
        @endif
    </a>

    <div class="dropdown-menu dropdown-menu-end header-card border-0 p-0" style="width: 320px;">
        <div class="head-container notification-head-container p-3 border-bottom">
            <h6 class="mb-0">Notifications</h6>
        </div>

        <div class="notification-offcanvas-body app-scroll" style="max-height: 320px; overflow-y: auto;">
            @forelse ($notifications as $notification)
                <a href="{{ $notification->data['url'] ?? route('sjadmin.notifications.index') }}"
                   class="notification-message head-box p-3 border-bottom d-block text-decoration-none {{ $notification->read_at ? '' : 'bg-light-primary bg-opacity-10' }}">
                    <p class="mb-1">{{ $notification->data['message'] ?? 'New notification' }}</p>
                    <small class="text-secondary">{{ $notification->created_at->diffForHumans() }}</small>
                </a>
            @empty
                <div class="p-4 text-center">
                    <p class="text-secondary mb-0">When you have any notifications, they'll show up here.</p>
                </div>
            @endforelse
        </div>

        <div class="text-center p-2 border-top">
            <a href="{{ route('sjadmin.notifications.index') }}" class="small">View all notifications</a>
        </div>
    </div>
</li>
