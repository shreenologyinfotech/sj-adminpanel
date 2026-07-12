@extends('sjadminpanel::layouts.app')
@section('title', 'File Manager')
@section('page-title', 'File Manager')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="path" value="{{ $path }}">
                <div class="col-md-8">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search current folder...">
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-light-primary">Search</button>
                    @if ($parentPath)
                        <a href="{{ route('sjadmin.files.index', ['path' => $parentPath]) }}" class="btn btn-light-secondary">Up</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">Upload</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sjadmin.files.upload') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="path" value="{{ $path }}">
                        <input type="file" name="files[]" class="form-control mb-3" multiple required>
                        <button class="btn btn-primary text-white">Upload Files</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">New Folder</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sjadmin.files.folder') }}">
                        @csrf
                        <input type="hidden" name="path" value="{{ $path }}">
                        <input type="text" name="name" class="form-control mb-3" placeholder="Folder name" required>
                        <button class="btn btn-light-primary">Create Folder</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><code>{{ $path }}</code></h6>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr><th>Name</th><th>Size</th><th>Modified</th><th></th></tr>
                        </thead>
                        <tbody>
                        @forelse ($folders as $folder)
                            <tr>
                                <td><i class="iconoir-folder text-warning me-1"></i><a href="{{ route('sjadmin.files.index', ['path' => $folder['path']]) }}">{{ $folder['name'] }}</a></td>
                                <td>Folder</td>
                                <td></td>
                                <td class="text-end">
                                    @include('sjadminpanel::files._actions', ['path' => $folder['path'], 'download' => false])
                                </td>
                            </tr>
                        @empty
                        @endforelse

                        @forelse ($files as $file)
                            <tr>
                                <td><i class="iconoir-page me-1"></i>{{ $file['name'] }}</td>
                                <td>{{ number_format($file['size'] / 1024, 2) }} KB</td>
                                <td>{{ \Illuminate\Support\Carbon::createFromTimestamp($file['modified'])->format('Y-m-d H:i:s') }}</td>
                                <td class="text-end">
                                    @include('sjadminpanel::files._actions', ['path' => $file['path'], 'download' => true])
                                </td>
                            </tr>
                        @empty
                            @if ($folders->isEmpty())
                                <tr><td colspan="4" class="text-center text-muted py-4">This folder is empty.</td></tr>
                            @endif
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
