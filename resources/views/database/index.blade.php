@extends('sjadminpanel::layouts.app')
@section('title', 'Database Manager')
@section('page-title', 'Database Manager')
@section('content')
    @if (! empty($databaseError))
        <div class="alert alert-light-danger" role="alert">{{ $databaseError }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Table</th><th>Columns</th><th></th></tr></thead>
                <tbody>
                @forelse ($tables as $table)
                    <tr>
                        <td><i class="iconoir-database me-1"></i> {{ $table['name'] }}</td>
                        <td>{{ $table['columns'] }}</td>
                        <td class="text-end">
                            <form action="{{ route('sjadmin.database.tables.destroy', $table['name']) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light-danger" onclick="return confirm('Drop this table? This cannot be undone.')">Drop</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">No tables found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
