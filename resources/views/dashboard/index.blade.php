@extends('sjadminpanel::layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-secondary mb-1">Total Users</p>
                        <h3 class="mb-0">{{ $usersCount }}</h3>
                    </div>
                    <span class="text-light-primary h-50 w-50 d-flex-center b-r-15">
                        <i class="iconoir-group f-s-24"></i>
                    </span>
                </div>
            </div>
        </div>

        @foreach ($widgets as $widget)
            @if (($widget['type'] ?? null) === 'stat-card')
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-secondary mb-1">{{ $widget['title'] }}</p>
                                <h3 class="mb-0">{{ $widget['value'] }}</h3>
                            </div>
                            <span class="text-light-info h-50 w-50 d-flex-center b-r-15">
                                <i class="{{ $widget['icon'] ?? 'iconoir-stats-up-square' }} f-s-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Latest Users</h6></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr><th>Name</th><th>Email</th><th>Joined</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($recentUsers as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-secondary py-4">No users yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @foreach ($widgets as $widget)
            @if (($widget['type'] ?? null) === 'status')
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">{{ $widget['title'] }}</h6></div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">PHP Version: <strong>{{ $widget['php_version'] }}</strong></li>
                                <li class="mb-2">Laravel Version: <strong>{{ $widget['laravel_version'] }}</strong></li>
                                <li class="mb-2">Environment: <strong>{{ $widget['environment'] }}</strong></li>
                                <li>Debug Mode: <strong>{{ $widget['debug'] ? 'On' : 'Off' }}</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endsection
