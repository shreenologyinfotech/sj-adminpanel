@php
    $sidebarItems = app(\Safarjaisur\AdminPanel\Contracts\Repositories\MenuRepositoryInterface::class)->itemsFor('admin');
    $currentUser = auth('sjadmin')->user();
    $canSeeMenuItem = fn ($item) => ! $item->permission || $currentUser?->hasPermission($item->permission);
    $isMenuItemActive = function ($item) use (&$isMenuItemActive) {
        $active = false;

        if ($item->route) {
            $patterns = [$item->route];

            if (substr_count($item->route, '.') >= 2) {
                $patterns[] = \Illuminate\Support\Str::beforeLast($item->route, '.') . '.*';
            }

            $active = request()->routeIs(...$patterns);
        } elseif ($item->url && $item->url !== '#') {
            $active = request()->is(ltrim($item->url, '/'));
        }

        foreach ($item->children as $child) {
            $active = $active || $isMenuItemActive($child);
        }

        return $active;
    };
@endphp
<nav>
    <div class="app-logo">
        <a class="logo d-inline-block" href="{{ route('sjadmin.dashboard') }}">
            <img alt="{{ config('app.name') }}" src="{{ asset(config('sjadminpanel.theme.logo')) }}">
        </a>
        <span class="bg-light-primary toggle-semi-nav">
            <i class="ti ti-chevrons-right f-s-20"></i>
        </span>
    </div>

    <div class="app-nav" id="app-simple-bar">
        <ul class="main-nav p-0 mt-2">
            @foreach ($sidebarItems as $item)
                @include('sjadminpanel::partials.sidebar-item', [
                    'item' => $item,
                    'depth' => 0,
                    'canSeeMenuItem' => $canSeeMenuItem,
                    'isMenuItemActive' => $isMenuItemActive,
                ])
            @endforeach
        </ul>
    </div>
</nav>
