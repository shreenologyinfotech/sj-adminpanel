@extends('sjadminpanel::layouts.app')
@section('title', 'Settings')
@section('page-title', 'Settings')
@section('content')
    <form method="POST" action="{{ route('sjadmin.settings.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <ul class="nav nav-pills" role="tablist">
                    @foreach ($groups as $group => $settings)
                        @php($groupId = \Illuminate\Support\Str::slug($group))
                        <li class="nav-item" role="presentation">
                            <button class="nav-link @if($loop->first) active @endif" id="settings-{{ $groupId }}-tab" data-bs-toggle="pill" data-bs-target="#settings-{{ $groupId }}" type="button" role="tab" aria-controls="settings-{{ $groupId }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                {{ \Illuminate\Support\Str::headline($group) }}
                            </button>
                        </li>
                    @endforeach
                </ul>
                <button class="btn btn-primary text-white">Save Settings</button>
            </div>

            <div class="card-body">
                <div class="tab-content">
                    @foreach ($groups as $group => $settings)
                        @php($groupId = \Illuminate\Support\Str::slug($group))
                        <div class="tab-pane fade @if($loop->first) show active @endif" id="settings-{{ $groupId }}" role="tabpanel" aria-labelledby="settings-{{ $groupId }}-tab">
                            <div class="row g-4">
                                @foreach ($settings as $setting)
                                    <div class="col-xl-6">
                                        <div class="border rounded p-3 h-100">
                                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                                <div>
                                                    <label class="form-label mb-1">{{ \Illuminate\Support\Str::headline($setting->key) }}</label>
                                                    <div class="text-muted small">{{ $setting->key }}</div>
                                                </div>
                                                <span class="badge bg-light-secondary">{{ $setting->type }}</span>
                                            </div>

                                            @switch($setting->type)
                                                @case('textarea')
                                                    <textarea name="settings[{{ $setting->key }}]" class="form-control" rows="4">{{ old("settings.{$setting->key}", $setting->value) }}</textarea>
                                                    @break

                                                @case('boolean')
                                                    <div class="form-check form-switch">
                                                        <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                                        <input type="checkbox" name="settings[{{ $setting->key }}]" value="1" class="form-check-input" @checked((bool) old("settings.{$setting->key}", $setting->value))>
                                                    </div>
                                                    @break

                                                @case('number')
                                                    <input type="number" name="settings[{{ $setting->key }}]" class="form-control" value="{{ old("settings.{$setting->key}", $setting->value) }}">
                                                    @break

                                                @case('image')
                                                    @if ($setting->value)
                                                        <div class="mb-2">
                                                            <img src="{{ \Illuminate\Support\Facades\Storage::disk(config('sjadminpanel.storage.disk'))->url($setting->value) }}" alt="{{ $setting->key }}" style="max-height: 74px;">
                                                        </div>
                                                    @endif
                                                    <input type="file" name="setting_files[{{ $setting->key }}]" class="form-control" accept="image/*">
                                                    @break

                                                @case('file')
                                                    @if ($setting->value)
                                                        <div class="mb-2"><code>{{ $setting->value }}</code></div>
                                                    @endif
                                                    <input type="file" name="setting_files[{{ $setting->key }}]" class="form-control">
                                                    @break

                                                @case('json')
                                                    <textarea name="settings[{{ $setting->key }}]" class="form-control font-monospace" rows="6">{{ old("settings.{$setting->key}", $setting->value) }}</textarea>
                                                    @break

                                                @default
                                                    <input type="{{ $setting->type === 'number' ? 'number' : 'text' }}" name="settings[{{ $setting->key }}]" class="form-control" value="{{ old("settings.{$setting->key}", $setting->value) }}">
                                            @endswitch

                                            @error("settings.{$setting->key}")
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror

                                            <div class="text-end mt-2">
                                                <button type="submit" form="delete-setting-{{ $setting->id }}" class="btn btn-sm btn-link text-danger p-0" onclick="return confirm('Delete this setting?')">Delete</button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </form>

    @foreach ($groups as $settings)
        @foreach ($settings as $setting)
            <form id="delete-setting-{{ $setting->id }}" method="POST" action="{{ route('sjadmin.settings.destroy', $setting) }}" class="d-none">
                @csrf @method('DELETE')
            </form>
        @endforeach
    @endforeach

    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0">Add Setting</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('sjadmin.settings.store') }}" class="row g-3">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Group</label>
                    <input type="text" name="group" class="form-control" placeholder="general" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Key</label>
                    <input type="text" name="key" class="form-control" placeholder="site.tagline" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        @foreach (['text', 'textarea', 'boolean', 'number', 'image', 'file', 'select', 'json'] as $type)
                            <option value="{{ $type }}">{{ \Illuminate\Support\Str::headline($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Value</label>
                    <input type="text" name="value" class="form-control">
                </div>
                <div class="col-12 text-end">
                    <button class="btn btn-light-primary">Add Setting</button>
                </div>
            </form>
        </div>
    </div>
@endsection
