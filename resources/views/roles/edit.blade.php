@extends('sjadminpanel::layouts.app')
@section('title', 'Edit Role')
@section('page-title', 'Edit Role')
@section('content')
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('sjadmin.roles.update', $role) }}">
            @csrf @method('PUT')
            @include('sjadminpanel::roles._form', ['role' => $role, 'permissions' => $permissions])
        </form>
    </div></div>
@endsection
