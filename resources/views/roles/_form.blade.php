<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $role->name ?? '') }}" required>
</div>
@if (! $role)
    <div class="mb-3">
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" required>
    </div>
@endif
<div class="mb-3">
    <label class="form-label">Permissions</label>
    @foreach ($permissions as $group => $items)
        <h6 class="mt-3">{{ $group }}</h6>
        @foreach ($items as $permission)
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                       id="perm-{{ $permission->id }}"
                       @checked(($role?->permissions->pluck('id')->contains($permission->id)))>
                <label class="form-check-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
            </div>
        @endforeach
    @endforeach
</div>
<button class="btn btn-primary text-white">Save</button>
