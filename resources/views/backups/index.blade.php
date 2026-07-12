@extends('sjadminpanel::layouts.app')
@section('title', 'Backup Manager')
@section('page-title', 'Backup Manager')
@section('content')
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <div>
                <h6 class="mb-1">Database Backups</h6>
                <p class="text-secondary mb-0 small">Create and download SQL backups for the current database.</p>
            </div>
            <form method="POST" action="{{ route('sjadmin.backups.store') }}">
                @csrf
                <button class="btn btn-primary text-white">
                    <i class="iconoir-database-export me-1"></i> Create Backup
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Size</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($backups as $backup)
                    <tr>
                        <td><i class="iconoir-archive me-1"></i> {{ $backup['name'] }}</td>
                        <td>{{ number_format($backup['size'] / 1024, 2) }} KB</td>
                        <td>{{ \Carbon\Carbon::createFromTimestamp($backup['modified'])->format('Y-m-d H:i:s') }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-light-primary" href="{{ route('sjadmin.backups.download', $backup['name']) }}">Download</a>
                            <form method="POST" action="{{ route('sjadmin.backups.restore', $backup['name']) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-light-warning" onclick="return confirm('Restore this backup? Current matching tables may be dropped and replaced.')">Restore</button>
                            </form>
                            <form method="POST" action="{{ route('sjadmin.backups.destroy', $backup['name']) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light-danger" onclick="return confirm('Delete this backup?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No backups found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
