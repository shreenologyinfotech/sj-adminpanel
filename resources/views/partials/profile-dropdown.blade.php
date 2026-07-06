<li class="header-profile">
    <a class="d-block head-icon" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <img alt="{{ $user?->name }}" class="rounded-circle" width="34" height="34" src="{{ $user?->avatarUrl() }}">
    </a>

    <div class="dropdown-menu dropdown-menu-end header-card border-0 p-2" style="width: 240px;">
        <div class="d-flex align-items-center p-2 border-bottom mb-2">
            <img alt="{{ $user?->name }}" class="rounded-circle me-2" width="40" height="40" src="{{ $user?->avatarUrl() }}">
            <div>
                <h6 class="mb-0">{{ $user?->name }}</h6>
                <small class="text-secondary">{{ $user?->email }}</small>
            </div>
        </div>

        <a class="dropdown-item" href="{{ route('sjadmin.profile.edit') }}">
            <i class="ti ti-user me-2"></i> Profile
        </a>
        <a class="dropdown-item" href="{{ route('sjadmin.settings.index') }}">
            <i class="ti ti-settings me-2"></i> Settings
        </a>

        <div class="dropdown-divider"></div>

        <form method="POST" action="{{ route('sjadmin.logout') }}">
            @csrf
            <button type="submit" class="dropdown-item text-danger">
                <i class="ti ti-logout me-2"></i> Sign Out
            </button>
        </form>
    </div>
</li>
