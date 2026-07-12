@extends('sjadminpanel::layouts.app')
@section('title', 'Activity Log')
@section('page-title', 'Activity Log')
@section('content')
    <div class="card">
        <div class="card-header">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search activity...">
                </div>
                <div class="col-md-2">
                    <select name="user_id" class="form-select">
                        <option value="">All Users</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="action" class="form-select">
                        <option value="">All Actions</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}" placeholder="From">
                </div>
                <div class="col-md-2">
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}" placeholder="To">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-light-primary w-100">Filter</button>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr><th>When</th><th>User</th><th>Action</th><th>Subject</th><th>IP</th></tr>
                </thead>
                <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                        <td>{{ $log->user?->name ?: 'System' }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ class_basename((string) $log->subject_type) }} {{ $log->subject_id }}</td>
                        <td>{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No activity recorded.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $logs->links() }}</div>
    </div>
@endsection
