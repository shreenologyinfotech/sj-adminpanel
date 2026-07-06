@extends('sjadminpanel::layouts.app')
@section('title', 'Settings')
@section('page-title', 'Settings')
@section('content')
    <form method="POST" action="{{ route('sjadmin.settings.update') }}">
        @csrf @method('PUT')
        @foreach ($groups as $group => $settings)
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0 text-capitalize">{{ $group }}</h6></div>
                <div class="card-body">
                    @foreach ($settings as $setting)
                        <div class="mb-3">
                            <label class="form-label">{{ \Illuminate\Support\Str::headline($setting->key) }}</label>
                            @if ($setting->type === 'textarea')
                                <textarea name="settings[{{ $setting->key }}]" class="form-control">{{ $setting->value }}</textarea>
                            @else
                                <input type="text" name="settings[{{ $setting->key }}]" class="form-control" value="{{ $setting->value }}">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
        <button class="btn btn-primary text-white">Save Settings</button>
    </form>
@endsection
