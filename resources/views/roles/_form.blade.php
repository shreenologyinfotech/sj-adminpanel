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

    @php
        $systemGroups = $permissions->reject(fn ($items, $group) => in_array($group, $breadNames ?? [], true));
        $dataGroups = $permissions->only($breadNames ?? []);
    @endphp

    @if ($systemGroups->isNotEmpty())
        <h6 class="text-uppercase small text-secondary mt-3">System Permissions</h6>
        @foreach ($systemGroups as $group => $items)
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
    @endif

    @if ($dataGroups->isNotEmpty())
        <h6 class="text-uppercase small text-secondary mt-4">Data Permissions</h6>
        @foreach ($dataGroups as $group => $items)
            <div class="border rounded p-2 mt-2">
                <div class="d-flex justify-content-between align-items-center">
                    <strong>{{ $group }}</strong>
                    <button type="button" class="btn btn-sm btn-link js-toggle-group" data-group="{{ \Illuminate\Support\Str::slug($group) }}">Select all</button>
                </div>
                @foreach ($items as $permission)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input js-group-{{ \Illuminate\Support\Str::slug($group) }}" type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                               id="perm-{{ $permission->id }}"
                               @checked(($role?->permissions->pluck('id')->contains($permission->id)))>
                        <label class="form-check-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                    </div>
                @endforeach
            </div>
        @endforeach

        <script>
            document.querySelectorAll('.js-toggle-group').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const boxes = document.querySelectorAll('.js-group-' + btn.dataset.group);
                    const shouldCheck = Array.from(boxes).some((b) => !b.checked);
                    boxes.forEach((b) => { b.checked = shouldCheck; });
                });
            });
        </script>
    @endif
</div>
<button class="btn btn-primary text-white">Save</button>
