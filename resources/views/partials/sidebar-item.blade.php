@if ($canSeeMenuItem($item))
    @php
        $visibleChildren = $item->children
            ->filter(fn ($child) => $canSeeMenuItem($child))
            ->values();
        $isActive = $isMenuItemActive($item);
        $collapseId = 'nav-item-' . $item->id;
        $icon = $item->icon ?: 'iconoir-menu';
        $indent = $depth > 0 ? 'padding-left: ' . min($depth * 16, 48) . 'px' : null;
    @endphp

    @if ($visibleChildren->isNotEmpty())
        <li>
            <a class="{{ $isActive ? '' : 'collapsed' }}"
               data-bs-toggle="collapse"
               href="#{{ $collapseId }}"
               aria-expanded="{{ $isActive ? 'true' : 'false' }}"
               @if ($indent) style="{{ $indent }}" @endif>
                <i class="{{ $icon }}"></i>
                {{ $item->title }}
            </a>
            <ul class="collapse @if($isActive) show @endif" id="{{ $collapseId }}">
                @foreach ($visibleChildren as $child)
                    @include('sjadminpanel::partials.sidebar-item', [
                        'item' => $child,
                        'depth' => $depth + 1,
                        'canSeeMenuItem' => $canSeeMenuItem,
                        'isMenuItemActive' => $isMenuItemActive,
                    ])
                @endforeach
            </ul>
        </li>
    @else
        <li class="no-sub">
            <a class="{{ $isActive ? 'active' : '' }}"
               href="{{ $item->resolvedUrl() }}"
               target="{{ $item->target }}"
               @if ($indent) style="{{ $indent }}" @endif>
                <i class="{{ $icon }}"></i> {{ $item->title }}
            </a>
        </li>
    @endif
@endif
