@extends('sjadminpanel::layouts.app')
@section('title', 'New BREAD')
@section('page-title', 'New BREAD')
@section('content')
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('sjadmin.bread.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Table</label>
                <select name="table_name" class="form-select" required>
                    @foreach ($tables as $table)
                        <option value="{{ $table }}">{{ $table }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Icon (Tabler icon class)</label>
                <input type="text" name="icon" class="form-control" placeholder="ti ti-table">
            </div>
            <button class="btn btn-primary text-white">Create</button>
        </form>
    </div></div>
@endsection
