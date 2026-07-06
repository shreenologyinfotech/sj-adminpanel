@extends('sjadminpanel::layouts.app')
@section('title', 'Database Manager')
@section('page-title', 'Database Manager')
@section('content')
    <div class="card">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Table</th><th>Columns</th><th></th></tr></thead>
                <tbody>
                @foreach ($tables as $table)
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
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
