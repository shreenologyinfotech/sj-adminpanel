@extends('sjadminpanel::layouts.app')
@section('title', 'Users')
@section('page-title', 'Users')
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search users..." value="{{ request('search') }}">
                <button class="btn btn-light-primary">Search</button>
            </form>
            <a href="{{ route('sjadmin.users.create') }}" class="btn btn-primary text-white">Add User</a>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Name</th><th>Email</th><th>Roles</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->roles->pluck('name')->join(', ') }}</td>
                        <td><span class="badge bg-light-{{ $user->status === 'active' ? 'success' : 'danger' }}">{{ $user->status }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('sjadmin.users.edit', $user) }}" class="btn btn-sm btn-light-primary">Edit</a>
                            <form action="{{ route('sjadmin.users.destroy', $user) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light-danger" onclick="return confirm('Delete this user?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-secondary py-4">No users found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $users->links() }}</div>
    </div>
@endsection
