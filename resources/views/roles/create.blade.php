@extends('sjadminpanel::layouts.app')
@section('title', 'Add Role')
@section('page-title', 'Add Role')
@section('content')
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('sjadmin.roles.store') }}">
            @csrf
            @include('sjadminpanel::roles._form', ['role' => null, 'permissions' => $permissions])
        </form>
    </div></div>
@endsection
