@extends('sjadminpanel::layouts.app')
@section('title', 'Menu Builder')
@section('page-title', 'Menu Builder: ' . $menu->name)
@section('content')
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Menu Items</h6></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead><tr><th>Order</th><th>Title</th><th>Route / URL</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $item->order }}</td>
                                <td><i class="{{ $item->icon }} me-1"></i>{{ $item->title }}</td>
                                <td><code>{{ $item->route ?: $item->url }}</code></td>
                                <td class="text-end">
                                    <form action="{{ route('sjadmin.menu.destroy', $item) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light-danger" onclick="return confirm('Remove?')">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Add Menu Item</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sjadmin.menu.store') }}">
                        @csrf
                        <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                        <div class="mb-3"><label class="form-label">Title</label><input class="form-control" name="title" required></div>
                        <div class="mb-3"><label class="form-label">Icon</label><input class="form-control" name="icon" placeholder="iconoir-star"></div>
                        <div class="mb-3"><label class="form-label">Route name</label><input class="form-control" name="route"></div>
                        <div class="mb-3"><label class="form-label">or URL</label><input class="form-control" name="url"></div>
                        <button class="btn btn-primary text-white">Add</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
