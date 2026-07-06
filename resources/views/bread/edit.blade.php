@extends('sjadminpanel::layouts.app')
@section('title', 'Edit BREAD')
@section('page-title', 'Edit BREAD: ' . $bread->name)
@section('content')
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('sjadmin.bread.update', $bread) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ $bread->name }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Icon</label>
                <input type="text" name="icon" class="form-control" value="{{ $bread->icon }}">
            </div>
            <h6 class="mt-4">Fields</h6>
            <table class="table">
                <thead><tr><th>Name</th><th>Type</th><th>Browse</th><th>Read</th><th>Edit</th><th>Add</th></tr></thead>
                <tbody>
                @foreach ($bread->fields as $i => $field)
                    <tr>
                        <td>
                            {{ $field['name'] }}
                            <input type="hidden" name="fields[{{ $i }}][name]" value="{{ $field['name'] }}">
                        </td>
                        <td>
                            {{ $field['type'] }}
                            <input type="hidden" name="fields[{{ $i }}][type]" value="{{ $field['type'] }}">
                        </td>
                        @foreach (['browse', 'read', 'edit', 'add'] as $flag)
                            <td class="text-center">
                                <input type="checkbox" name="fields[{{ $i }}][{{ $flag }}]" value="1" @checked($field[$flag] ?? false)>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
            <button class="btn btn-primary text-white">Save</button>
        </form>
    </div></div>
@endsection
