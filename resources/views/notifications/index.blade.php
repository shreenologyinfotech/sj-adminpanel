@extends('sjadminpanel::layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-3">
                <form method="POST" action="{{ route('sjadmin.notifications.read-all') }}">
                    @csrf
                    <button class="btn btn-light-secondary btn-sm">Mark all as read</button>
                </form>
            </div>

            @forelse ($notifications as $notification)
                <div class="d-flex justify-content-between align-items-start border-bottom py-3 {{ $notification->read_at ? '' : 'bg-light-primary bg-opacity-10' }}">
                    <div>
                        @if ($notification->data['url'] ?? null)
                            <a href="{{ $notification->data['url'] }}">{{ $notification->data['message'] ?? 'Notification' }}</a>
                        @else
                            {{ $notification->data['message'] ?? 'Notification' }}
                        @endif
                        <div class="small text-secondary">{{ $notification->created_at->diffForHumans() }}</div>
                    </div>
                    @unless ($notification->read_at)
                        <form method="POST" action="{{ route('sjadmin.notifications.read', $notification->id) }}">
                            @csrf
                            <button class="btn btn-sm btn-light-secondary">Mark read</button>
                        </form>
                    @endunless
                </div>
            @empty
                <p class="text-secondary mb-0">You don't have any notifications yet.</p>
            @endforelse

            <div class="mt-3">{{ $notifications->links() }}</div>
        </div>
    </div>
@endsection
