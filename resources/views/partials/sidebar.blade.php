@php
    $sidebarItems = app(\Safarjaisur\AdminPanel\Contracts\Repositories\MenuRepositoryInterface::class)->itemsFor('admin');
    $currentUser = auth('sjadmin')->user();
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
                @continue($item->permission && ! $currentUser?->hasPermission($item->permission))

                @if ($item->children->isNotEmpty())
                    <li>
                        <a class="{{ request()->routeIs($item->route . '*') ? '' : 'collapsed' }}"
                           data-bs-toggle="collapse"
                           href="#nav-item-{{ $item->id }}"
                           aria-expanded="{{ request()->routeIs($item->route . '*') ? 'true' : 'false' }}">
                            <i class="{{ $item->icon }}"></i>
                            {{ $item->title }}
                        </a>
                        <ul class="collapse @if(request()->routeIs($item->route.'*')) show @endif" id="nav-item-{{ $item->id }}">
                            @foreach ($item->children as $child)
                                <li><a href="{{ $child->resolvedUrl() }}" target="{{ $child->target }}">{{ $child->title }}</a></li>
                            @endforeach
                        </ul>
                    </li>
                @else
                    <li class="no-sub">
                        <a class="{{ $item->route && request()->routeIs($item->route) ? 'active' : '' }}"
                           href="{{ $item->resolvedUrl() }}"
                           target="{{ $item->target }}">
                            <i class="{{ $item->icon }}"></i> {{ $item->title }}
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    </div>
</nav>
