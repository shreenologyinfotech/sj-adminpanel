@extends('sjadminpanel::layouts.app')
@section('title', 'Media Manager')
@section('page-title', 'Media Manager')
@section('content')
    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap gap-3 justify-content-between">
            <form method="POST" action="{{ route('sjadmin.media.store') }}" enctype="multipart/form-data" class="flex-grow-1">
                @csrf
                <input type="hidden" name="folder" value="{{ request('folder') }}">
                <div class="input-group">
                    <input type="file" name="files[]" class="form-control" multiple required>
                    <button class="btn btn-primary text-white">Upload</button>
                </div>
            </form>

            <form method="POST" action="{{ route('sjadmin.media.folder') }}" class="d-flex gap-2">
                @csrf
                <input type="hidden" name="folder" value="{{ request('folder') }}">
                <input type="text" name="name" class="form-control" placeholder="New folder name" required>
                <button class="btn btn-light-secondary text-nowrap">New Folder</button>
            </form>
        </div>
    </div>

    @if (request('folder'))
        @php
            $trimmedFolder = trim(request('folder'), '/');
            $parentFolder = str_contains($trimmedFolder, '/') ? \Illuminate\Support\Str::beforeLast($trimmedFolder, '/') : '';
        @endphp
        <p class="text-secondary mb-3">
            <a href="{{ route('sjadmin.media.index', ['folder' => $parentFolder]) }}">&larr; Up a level</a>
            &nbsp;/&nbsp;{{ $trimmedFolder }}
        </p>
    @endif

    <div class="row">
        @forelse ($folders as $folder)
            <div class="col-md-2 col-6 mb-3 text-center">
                <a href="{{ route('sjadmin.media.index', ['folder' => \Illuminate\Support\Str::after($folder, config('sjadminpanel.storage.media_path') . '/')]) }}">
                    <i class="iconoir-folder f-s-40 text-warning"></i>
                    <p class="mb-0 small">{{ basename($folder) }}</p>
                </a>
            </div>
        @empty
        @endforelse

        @forelse ($files as $file)
            @php
                $isImage = $thumbnails->isImage($file);
                $thumbPath = $thumbnails->thumbnailPath($file, 'small');
                $thumbUrl = $isImage && $disk->exists($thumbPath) ? $disk->url($thumbPath) : ($isImage ? $disk->url($file) : null);
            @endphp
            <div class="col-md-2 col-6 mb-3 text-center">
                @if ($thumbUrl)
                    <a href="{{ $disk->url($file) }}" target="_blank">
                        <img src="{{ $thumbUrl }}" class="img-fluid rounded mb-1" style="height: 90px; object-fit: cover;" alt="{{ basename($file) }}">
                    </a>
                @else
                    <i class="iconoir-page f-s-40 text-primary"></i>
                @endif
                <p class="mb-1 small text-truncate">{{ basename($file) }}</p>
                <div class="d-flex gap-1 justify-content-center">
                    <button type="button" class="btn btn-sm btn-light-secondary" data-bs-toggle="modal" data-bs-target="#rename-{{ md5($file) }}">Rename</button>
                    <form action="{{ route('sjadmin.media.destroy') }}" method="POST">
                        @csrf @method('DELETE')
                        <input type="hidden" name="path" value="{{ $file }}">
                        <button class="btn btn-sm btn-light-danger" onclick="return confirm('Delete this file?')">Delete</button>
                    </form>
                </div>
            </div>

            <div class="modal fade" id="rename-{{ md5($file) }}" tabindex="-1">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('sjadmin.media.rename') }}" class="modal-content">
                        @csrf @method('PUT')
                        <input type="hidden" name="path" value="{{ $file }}">
                        <div class="modal-header">
                            <h5 class="modal-title">Rename {{ basename($file) }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="text" name="name" class="form-control" value="{{ pathinfo($file, PATHINFO_FILENAME) }}" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary text-white">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-secondary">No files in this folder.</p>
        @endforelse
    </div>
@endsection
