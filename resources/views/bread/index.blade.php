@extends('sjadminpanel::layouts.app')
@section('title', 'BREAD Builder')
@section('page-title', 'BREAD Builder')
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-end">
            <a href="{{ route('sjadmin.bread.create') }}" class="btn btn-primary text-white">New BREAD</a>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Name</th><th>Table</th><th>Fields</th><th></th></tr></thead>
                <tbody>
                @forelse ($breads as $bread)
                    <tr>
                        <td><i class="{{ $bread->icon ?? 'iconoir-view-grid' }} me-1"></i> {{ $bread->name }}</td>
                        <td><code>{{ $bread->table_name }}</code></td>
                        <td>{{ count($bread->fields) }} fields</td>
                        <td class="text-end">
                            <a href="{{ route('sjadmin.bread.edit', $bread) }}" class="btn btn-sm btn-light-primary">Edit</a>
                            <form action="{{ route('sjadmin.bread.destroy', $bread) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light-danger" onclick="return confirm('Delete?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-secondary py-4">No BREAD resources yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $breads->links() }}</div>
    </div>
@endsection
