<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
    </div>
    @if (! $user)
        <div class="col-md-6 mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
    @else
        <div class="col-md-6 mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                @foreach (['active', 'inactive', 'banned'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $user->status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <div class="col-12 mb-3">
        <label class="form-label">Roles</label>
        <div>
            @foreach ($roles as $role)
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}"
                           id="role-{{ $role->id }}"
                           @checked(($user?->roles->pluck('id')->contains($role->id)))>
                    <label class="form-check-label" for="role-{{ $role->id }}">{{ $role->name }}</label>
                </div>
            @endforeach
        </div>
    </div>
</div>
<button class="btn btn-primary text-white">Save</button>
