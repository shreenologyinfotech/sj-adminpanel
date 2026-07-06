@extends('sjadminpanel::layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('sjadmin.users.update', $user) }}">
                @csrf @method('PUT')
                @include('sjadminpanel::users._form', ['user' => $user, 'roles' => $roles])
            </form>
        </div>
    </div>
@endsection
