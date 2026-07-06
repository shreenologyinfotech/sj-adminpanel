@if (! empty($breadcrumbs ?? []))
    <div class="page-header d-flex align-items-center justify-content-between mb-3">
        <h4 class="mb-0">@yield('page-title', 'Dashboard')</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('sjadmin.dashboard') }}">Home</a></li>
                @foreach ($breadcrumbs as $label => $url)
                    @if ($loop->last)
                        <li class="breadcrumb-item active" aria-current="page">{{ $label }}</li>
                    @else
                        <li class="breadcrumb-item"><a href="{{ $url }}">{{ $label }}</a></li>
                    @endif
                @endforeach
            </ol>
        </nav>
    </div>
@else
    <div class="page-header mb-3">
        <h4 class="mb-0">@yield('page-title', 'Dashboard')</h4>
    </div>
@endif
