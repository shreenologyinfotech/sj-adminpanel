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
@endsection
