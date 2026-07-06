@extends('sjadminpanel::layouts.app')
@section('title', 'Roles')
@section('page-title', 'Roles & Permissions')
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-end">
            <a href="{{ route('sjadmin.roles.create') }}" class="btn btn-primary text-white">Add Role</a>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Name</th><th>Permissions</th><th></th></tr></thead>
                <tbody>
                @forelse ($roles as $role)
                    <tr>
                        <td>{{ $role->name }}</td>
                        <td>{{ $role->permissions->count() }} permissions</td>
                        <td class="text-end">
                            <a href="{{ route('sjadmin.roles.edit', $role) }}" class="btn btn-sm btn-light-primary">Edit</a>
                            <form action="{{ route('sjadmin.roles.destroy', $role) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light-danger" onclick="return confirm('Delete this role?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-secondary py-4">No roles found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $roles->links() }}</div>
    </div>
@endsection
