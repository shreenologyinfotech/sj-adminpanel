@extends('sjadminpanel::layouts.app')
@section('title', 'Log Viewer')
@section('page-title', 'Log Viewer')
@section('content')
    <div class="row">
        <div class="col-lg-3 mb-3">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Log Files</h6></div>
                <div class="list-group list-group-flush">
                    @forelse ($files as $file)
                        <a class="list-group-item list-group-item-action {{ $selected === $file['name'] ? 'active' : '' }}"
                           href="{{ route('sjadmin.logs.index', ['file' => $file['name']]) }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-truncate">{{ $file['name'] }}</span>
                                <small>{{ number_format($file['size'] / 1024, 1) }} KB</small>
                            </div>
                        </a>
                    @empty
                        <div class="list-group-item text-muted">No log files found.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <h6 class="mb-0">{{ $selected ?: 'No log selected' }}</h6>
                        <small class="text-secondary">Showing last {{ $maxLines }} lines.</small>
                    </div>
                    @if ($selected)
                        <div>
                            <a class="btn btn-sm btn-light-primary" href="{{ route('sjadmin.logs.download', $selected) }}">Download</a>
                            <form method="POST" action="{{ route('sjadmin.logs.destroy', $selected) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light-danger" onclick="return confirm('Delete this log file?')">Delete</button>
                            </form>
                        </div>
                    @endif
                </div>
                <div class="card-body border-bottom">
                    <form method="GET" class="row g-2">
                        <input type="hidden" name="file" value="{{ $selected }}">
                        <div class="col-md-7">
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search log lines...">
                        </div>
                        <div class="col-md-3">
                            <select name="level" class="form-select">
                                <option value="">All levels</option>
                                @foreach ($levels as $level)
                                    <option value="{{ $level }}" @selected(request('level') === $level)>{{ strtoupper($level) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 text-end">
                            <button class="btn btn-light-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
                <div class="card-body p-0">
                    @if ($selected)
                        <pre class="mb-0 p-3 bg-dark text-white small overflow-auto" style="max-height: 650px; white-space: pre-wrap;">@forelse ($lines as $line){{ $line }}
@empty
This log file is empty.
@endforelse</pre>
                    @else
                        <div class="text-center text-muted py-4">No log file selected.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
