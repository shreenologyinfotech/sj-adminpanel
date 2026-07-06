@extends('sjadminpanel::layouts.app')
@section('title', 'Permissions')
@section('page-title', 'Permissions')
@section('content')
    <div class="card">
        <div class="card-header">
            <form method="POST" action="{{ route('sjadmin.permissions.store') }}" class="row g-2">
                @csrf
                <div class="col-md-4"><input class="form-control" name="name" placeholder="Name" required></div>
                <div class="col-md-3"><input class="form-control" name="slug" placeholder="slug" required></div>
                <div class="col-md-3"><input class="form-control" name="group" placeholder="Group" required></div>
                <div class="col-md-2"><button class="btn btn-primary text-white w-100">Add</button></div>
            </form>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Name</th><th>Slug</th><th>Group</th><th></th></tr></thead>
                <tbody>
                @forelse ($permissions as $permission)
                    <tr>
                        <td>{{ $permission->name }}</td>
                        <td><code>{{ $permission->slug }}</code></td>
                        <td>{{ $permission->group }}</td>
                        <td class="text-end">
                            <form action="{{ route('sjadmin.permissions.destroy', $permission) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light-danger" onclick="return confirm('Delete?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-secondary py-4">No permissions found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $permissions->links() }}</div>
    </div>
@endsection
