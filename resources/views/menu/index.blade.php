@extends('sjadminpanel::layouts.app')
@section('title', 'Menu Builder')
@section('page-title', 'Menu Builder: ' . $menu->name)
@section('content')
    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Sidebar Items</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">Order</th>
                                    <th>Item</th>
                                    <th>Destination</th>
                                    <th>Access</th>
                                    <th style="width: 280px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>{{ $item->order }}</td>
                                    <td>
                                        <span class="d-inline-block" style="padding-left: {{ $item->depth * 22 }}px">
                                            @if ($item->depth > 0)
                                                <span class="text-muted me-1">-></span>
                                            @endif
                                            <i class="{{ $item->icon ?: 'iconoir-menu' }} me-1"></i>{{ $item->title }}
                                        </span>
                                    </td>
                                    <td><code>{{ $item->route ?: $item->url ?: '#' }}</code></td>
                                    <td>{{ $item->permission ?: 'Public' }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-light-primary" type="button" data-bs-toggle="collapse" data-bs-target="#menu-edit-{{ $item->id }}">Edit</button>
                                        <form action="{{ route('sjadmin.menu.destroy', $item) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-light-danger" onclick="return confirm('Remove this menu item?')">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                                <tr class="collapse" id="menu-edit-{{ $item->id }}">
                                    <td colspan="5">
                                        <form method="POST" action="{{ route('sjadmin.menu.update', $item) }}" class="row g-3">
                                            @csrf @method('PUT')
                                            <div class="col-md-4">
                                                <label class="form-label">Title</label>
                                                <input class="form-control" name="title" value="{{ $item->title }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Icon</label>
                                                <input class="form-control" name="icon" value="{{ $item->icon }}" placeholder="iconoir-star">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Order</label>
                                                <input type="number" min="0" class="form-control" name="order" value="{{ $item->order }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Target</label>
                                                <select name="target" class="form-select">
                                                    <option value="_self" @selected($item->target === '_self')>Same tab</option>
                                                    <option value="_blank" @selected($item->target === '_blank')>New tab</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Parent</label>
                                                <select name="parent_id" class="form-select">
                                                    <option value="">Root item</option>
                                                    @foreach ($allItems as $parent)
                                                        @continue($parent->id === $item->id)
                                                        <option value="{{ $parent->id }}" @selected($item->parent_id === $parent->id)>{{ $parent->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Route</label>
                                                <input class="form-control" list="sjadmin-routes" name="route" value="{{ $item->route }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">URL</label>
                                                <input class="form-control" name="url" value="{{ $item->url }}">
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label">Permission</label>
                                                <select name="permission" class="form-select">
                                                    <option value="">No permission required</option>
                                                    @foreach ($permissions as $permission)
                                                        <option value="{{ $permission->slug }}" @selected($item->permission === $permission->slug)>
                                                            {{ $permission->group }} / {{ $permission->name }} ({{ $permission->slug }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4 d-flex align-items-end justify-content-end">
                                                <button class="btn btn-primary text-white">Save Item</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No menu items yet.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Add Menu Item</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sjadmin.menu.store') }}" class="row g-3">
                        @csrf
                        <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                        <div class="col-12">
                            <label class="form-label">Title</label>
                            <input class="form-control" name="title" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Icon</label>
                            <input class="form-control" name="icon" placeholder="iconoir-star">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Order</label>
                            <input type="number" min="0" class="form-control" name="order" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Parent</label>
                            <select name="parent_id" class="form-select">
                                <option value="">Root item</option>
                                @foreach ($allItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Route</label>
                            <input class="form-control" list="sjadmin-routes" name="route">
                        </div>
                        <div class="col-12">
                            <label class="form-label">or URL</label>
                            <input class="form-control" name="url">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Permission</label>
                            <select name="permission" class="form-select">
                                <option value="">No permission required</option>
                                @foreach ($permissions as $permission)
                                    <option value="{{ $permission->slug }}">{{ $permission->group }} / {{ $permission->name }} ({{ $permission->slug }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Target</label>
                            <select name="target" class="form-select">
                                <option value="_self">Same tab</option>
                                <option value="_blank">New tab</option>
                            </select>
                        </div>
                        <div class="col-12 text-end">
                            <button class="btn btn-primary text-white">Add Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <datalist id="sjadmin-routes">
        @foreach ($routes as $route)
            <option value="{{ $route }}"></option>
        @endforeach
    </datalist>
@endsection
