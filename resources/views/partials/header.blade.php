@php
    $currentUser = auth('sjadmin')->user();
    $notifications = $currentUser?->notifications()->latest()->limit(6)->get() ?? collect();
@endphp
<header class="header-main">
    <div class="container-fluid">
        <div class="row">
            <div class="col-6 col-sm-4 d-flex align-items-center header-left p-0">
                <span class="header-toggle me-3" role="button" aria-label="Toggle sidebar">
                    <i class="iconoir-view-grid"></i>
                </span>
            </div>

            <div class="col-6 col-sm-8 d-flex align-items-center justify-content-end header-right p-0">
                <form method="GET" action="{{ route('sjadmin.search.index') }}" class="d-none d-md-flex me-3" style="max-width: 340px;">
                    <input type="text" name="q" class="form-control form-control-sm" value="{{ request('q') }}" placeholder="Search...">
                </form>
                <ul class="d-flex align-items-center">

                    {{-- Dark mode toggle --}}
                    <li class="header-theme">
                        <a class="head-icon" href="#" role="button" title="Toggle dark mode"
                           onclick="event.preventDefault(); document.body.classList.toggle('dark'); this.querySelector('i').classList.toggle('iconoir-sun-light'); this.querySelector('i').classList.toggle('iconoir-half-moon');">
                            <i class="iconoir-half-moon"></i>
                        </a>
                    </li>

                    @include('sjadminpanel::partials.notifications-dropdown', ['notifications' => $notifications])

                    @include('sjadminpanel::partials.profile-dropdown', ['user' => $currentUser])
                </ul>
            </div>
        </div>
    </div>
</header>
