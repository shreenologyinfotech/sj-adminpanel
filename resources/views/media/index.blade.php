@extends('sjadminpanel::layouts.app')
@section('title', 'Media Manager')
@section('page-title', 'Media Manager')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form method="POST" action="{{ route('sjadmin.media.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="folder" value="{{ request('folder') }}">
                <div class="input-group">
                    <input type="file" name="files[]" class="form-control" multiple required>
                    <button class="btn btn-primary text-white">Upload</button>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        @forelse ($folders as $folder)
            <div class="col-md-2 col-6 mb-3 text-center">
                <a href="{{ route('sjadmin.media.index', ['folder' => $folder]) }}">
                    <i class="iconoir-folder f-s-40 text-warning"></i>
                    <p class="mb-0 small">{{ basename($folder) }}</p>
                </a>
            </div>
        @empty
        @endforelse

        @forelse ($files as $file)
            <div class="col-md-2 col-6 mb-3 text-center">
                <i class="iconoir-media-image f-s-40 text-primary"></i>
                <p class="mb-0 small text-truncate">{{ basename($file) }}</p>
                <form action="{{ route('sjadmin.media.destroy') }}" method="POST">
                    @csrf @method('DELETE')
                    <input type="hidden" name="path" value="{{ $file }}">
                    <button class="btn btn-sm btn-light-danger mt-1" onclick="return confirm('Delete?')">Delete</button>
                </form>
            </div>
        @empty
            <p class="text-secondary">No files in this folder.</p>
        @endforelse
    </div>
@endsection
