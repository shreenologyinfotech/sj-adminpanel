@extends('sjadminpanel::layouts.app')
@section('title', 'Import ' . $bread->name)
@section('page-title', 'Import ' . $bread->name)
@section('content')
    <div class="card">
        <div class="card-body">
            <p class="text-secondary">
                Upload a CSV file. The first row must be a header naming the fields below exactly —
                any other columns in the file are ignored, and any of these fields missing from the file are left blank.
            </p>

            <ul class="mb-4">
                @foreach ($fields as $field)
                    <li><code>{{ $field['name'] }}</code> — {{ $field['label'] }}{{ $field['required'] ? ' (required)' : '' }}</li>
                @endforeach
            </ul>

            <form method="POST" action="{{ route('sjadmin.resources.import', $bread) }}" enctype="multipart/form-data">
                @csrf
                <div class="input-group mb-3">
                    <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                    <button class="btn btn-primary text-white">Import</button>
                </div>
                @error('file')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </form>

            <a href="{{ route('sjadmin.resources.index', $bread) }}" class="btn btn-light-secondary">Back to list</a>
        </div>
    </div>
@endsection
