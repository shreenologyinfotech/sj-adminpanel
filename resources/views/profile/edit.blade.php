@extends('sjadminpanel::layouts.app')
@section('title', 'Profile')
@section('page-title', 'My Profile')
@section('content')
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('sjadmin.profile.update') }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="mb-3 text-center">
                <img src="{{ $user->avatarUrl() }}" class="rounded-circle mb-2" width="90" height="90">
                <input type="file" name="avatar" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
            <button class="btn btn-primary text-white">Update Profile</button>
        </form>
    </div></div>

    <div class="card mt-3"><div class="card-body">
        <h5 class="mb-3">Two-Factor Authentication</h5>

        @if ($user->hasEnabledTwoFactorAuthentication())
            <p class="text-success"><i class="iconoir-shield-check"></i> Two-factor authentication is enabled.</p>

            <form method="POST" action="{{ route('sjadmin.profile.two-factor.recovery-codes') }}" class="d-inline">
                @csrf
                <button class="btn btn-light-secondary">Regenerate Recovery Codes</button>
            </form>
            <form method="POST" action="{{ route('sjadmin.profile.two-factor.disable') }}" class="d-inline">
                @csrf @method('DELETE')
                <button class="btn btn-light-danger" onclick="return confirm('Disable two-factor authentication?')">Disable 2FA</button>
            </form>

            @if (session('success') && $user->two_factor_recovery_codes)
                <div class="alert alert-light-warning mt-3">
                    <strong>Save these recovery codes somewhere safe.</strong> Each can be used once if you lose access to your authenticator app.
                    <ul class="mb-0 mt-2">
                        @foreach ($user->two_factor_recovery_codes as $code)
                            <li><code>{{ $code }}</code></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @elseif ($pendingSecret)
            <p>Scan this into your authenticator app (Google Authenticator, Authy, 1Password, etc.), then enter the 6-digit code it shows to confirm setup.</p>
            <p class="mb-2"><strong>Manual entry key:</strong> <code>{{ $pendingSecret }}</code></p>
            <p class="small text-secondary text-break">{{ $pendingQrCodeUrl }}</p>

            <form method="POST" action="{{ route('sjadmin.profile.two-factor.confirm') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-auto">
                    <label class="form-label">Confirmation Code</label>
                    <input type="text" name="code" class="form-control" inputmode="numeric" required autofocus>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary text-white">Confirm & Enable</button>
                </div>
            </form>
        @else
            <p class="text-secondary">Two-factor authentication is not enabled on your account.</p>
            <form method="POST" action="{{ route('sjadmin.profile.two-factor.enable') }}">
                @csrf
                <button class="btn btn-primary text-white">Enable Two-Factor Authentication</button>
            </form>
        @endif
    </div></div>
@endsection
