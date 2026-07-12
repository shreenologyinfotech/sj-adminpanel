@extends('sjadminpanel::layouts.app')
@section('title', 'Search')
@section('page-title', 'Global Search')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="d-flex">
                <input type="text" name="q" class="form-control me-2" value="{{ $query }}" placeholder="Search users, roles, menus, BREAD and settings...">
                <button class="btn btn-primary text-white">Search</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Type</th><th>Result</th><th>Description</th><th></th></tr></thead>
                <tbody>
                @forelse ($results as $result)
                    <tr>
                        <td><span class="badge bg-light-primary">{{ $result['type'] }}</span></td>
                        <td>{{ $result['title'] }}</td>
                        <td class="text-truncate" style="max-width: 480px;">{{ $result['description'] }}</td>
                        <td class="text-end"><a href="{{ $result['url'] }}" class="btn btn-sm btn-light-primary">Open</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">{{ $query === '' ? 'Enter a search term.' : 'No results found.' }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
