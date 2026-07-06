@extends('sjadminpanel::layouts.app')
@section('title', 'Add User')
@section('page-title', 'Add User')
@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('sjadmin.users.store') }}">
                @csrf
                @include('sjadminpanel::users._form', ['user' => null, 'roles' => $roles])
            </form>
        </div>
    </div>
@endsection
