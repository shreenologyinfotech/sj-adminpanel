@if ($download)
    <a href="{{ route('sjadmin.files.download', ['path' => $path]) }}" class="btn btn-sm btn-light-primary">Download</a>
@endif
<button class="btn btn-sm btn-light-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#rename-{{ md5($path) }}">Rename</button>
<form method="POST" action="{{ route('sjadmin.files.destroy') }}" class="d-inline">
    @csrf @method('DELETE')
    <input type="hidden" name="path" value="{{ $path }}">
    <button class="btn btn-sm btn-light-danger" onclick="return confirm('Delete this item?')">Delete</button>
</form>
<div class="collapse mt-2" id="rename-{{ md5($path) }}">
    <form method="POST" action="{{ route('sjadmin.files.rename') }}" class="d-flex justify-content-end gap-2">
        @csrf @method('PUT')
        <input type="hidden" name="path" value="{{ $path }}">
        <input type="text" name="name" class="form-control form-control-sm" value="{{ basename($path) }}" required>
        <button class="btn btn-sm btn-primary text-white">Save</button>
    </form>
</div>
