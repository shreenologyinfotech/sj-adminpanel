@php
    $components = [
        'text', 'textarea', 'editor', 'markdown', 'number', 'boolean', 'switch',
        'select', 'radio', 'checkbox', 'date', 'datetime', 'time', 'file',
        'image', 'multiple_images', 'json', 'code', 'color', 'tags', 'slug',
        'email', 'password', 'hidden',
    ];
@endphp
@extends('sjadminpanel::layouts.app')
@section('title', 'Edit BREAD')
@section('page-title', 'Edit BREAD: ' . $bread->name)
@section('content')
    <form method="POST" action="{{ route('sjadmin.bread.update', $bread) }}">
        @csrf @method('PUT')

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $bread->name }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Icon</label>
                        <input type="text" name="icon" class="form-control" value="{{ $bread->icon }}" placeholder="iconoir-view-grid">
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion" id="bread-fields">
            @foreach ($bread->fields as $i => $field)
                @php
                    $field = array_merge([
                        'label' => \Illuminate\Support\Str::headline($field['name']),
                        'component' => 'text',
                        'order' => $i + 1,
                        'width' => 'col-md-12',
                        'value_column' => 'id',
                        'display_column' => 'name',
                    ], $field);
                    $fieldId = 'bread-field-' . $i;
                @endphp

                <div class="accordion-item">
                    <h2 class="accordion-header" id="{{ $fieldId }}-heading">
                        <button class="accordion-button @if($i !== 0) collapsed @endif" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $fieldId }}" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}" aria-controls="{{ $fieldId }}">
                            <span class="badge bg-light-primary me-2">{{ $field['order'] }}</span>
                            {{ $field['label'] }} <code class="ms-2">{{ $field['name'] }}</code>
                        </button>
                    </h2>
                    <div id="{{ $fieldId }}" class="accordion-collapse collapse @if($i === 0) show @endif" aria-labelledby="{{ $fieldId }}-heading" data-bs-parent="#bread-fields">
                        <div class="accordion-body">
                            <input type="hidden" name="fields[{{ $i }}][name]" value="{{ $field['name'] }}">
                            <input type="hidden" name="fields[{{ $i }}][type]" value="{{ $field['type'] ?? $field['type_name'] ?? 'string' }}">
                            <input type="hidden" name="fields[{{ $i }}][type_name]" value="{{ $field['type_name'] ?? $field['type'] ?? 'string' }}">

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Label</label>
                                    <input type="text" name="fields[{{ $i }}][label]" class="form-control" value="{{ $field['label'] }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Component</label>
                                    <select name="fields[{{ $i }}][component]" class="form-select">
                                        @foreach ($components as $component)
                                            <option value="{{ $component }}" @selected(($field['component'] ?? 'text') === $component)>{{ \Illuminate\Support\Str::headline($component) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Order</label>
                                    <input type="number" min="0" name="fields[{{ $i }}][order]" class="form-control" value="{{ $field['order'] }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Grid Width</label>
                                    <input type="text" name="fields[{{ $i }}][width]" class="form-control" value="{{ $field['width'] }}" placeholder="col-md-12">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Validation</label>
                                    <input type="text" name="fields[{{ $i }}][validation]" class="form-control" value="{{ $field['validation'] ?? '' }}" placeholder="max:255">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Default Value</label>
                                    <input type="text" name="fields[{{ $i }}][default]" class="form-control" value="{{ $field['default'] ?? '' }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Placeholder</label>
                                    <input type="text" name="fields[{{ $i }}][placeholder]" class="form-control" value="{{ $field['placeholder'] ?? '' }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Options</label>
                                    <textarea name="fields[{{ $i }}][options]" class="form-control" rows="3" placeholder="value => Label, one per line">{{ $field['options'] ?? '' }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Help Text</label>
                                    <textarea name="fields[{{ $i }}][help_text]" class="form-control" rows="3">{{ $field['help_text'] ?? '' }}</textarea>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Tab</label>
                                    <input type="text" name="fields[{{ $i }}][tab]" class="form-control" value="{{ $field['tab'] ?? '' }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Panel</label>
                                    <input type="text" name="fields[{{ $i }}][panel]" class="form-control" value="{{ $field['panel'] ?? '' }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Slug From</label>
                                    <input type="text" name="fields[{{ $i }}][slug_from]" class="form-control" value="{{ $field['slug_from'] ?? '' }}" placeholder="title">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Related Model</label>
                                    <input type="text" name="fields[{{ $i }}][related_model]" class="form-control" value="{{ $field['related_model'] ?? '' }}" placeholder="App\\Models\\User">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Relationship Type</label>
                                    <input type="text" name="fields[{{ $i }}][relationship_type]" class="form-control" value="{{ $field['relationship_type'] ?? '' }}" placeholder="belongsTo">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Value Column</label>
                                    <input type="text" name="fields[{{ $i }}][value_column]" class="form-control" value="{{ $field['value_column'] ?? 'id' }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Display Column</label>
                                    <input type="text" name="fields[{{ $i }}][display_column]" class="form-control" value="{{ $field['display_column'] ?? 'name' }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Flags</label>
                                    <div class="d-flex flex-wrap gap-3 pt-2">
                                        @foreach (['required', 'nullable', 'unique', 'hidden', 'slug', 'translatable', 'relationship'] as $flag)
                                            <label class="form-check">
                                                <input type="checkbox" class="form-check-input" name="fields[{{ $i }}][{{ $flag }}]" value="1" @checked($field[$flag] ?? false)>
                                                {{ \Illuminate\Support\Str::headline($flag) }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Visibility</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        @foreach (['browse', 'read', 'edit', 'add'] as $flag)
                                            <label class="form-check">
                                                <input type="checkbox" class="form-check-input" name="fields[{{ $i }}][{{ $flag }}]" value="1" @checked($field[$flag] ?? false)>
                                                {{ ucfirst($flag) }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-end mt-3">
            <a href="{{ route('sjadmin.bread.index') }}" class="btn btn-light-secondary">Cancel</a>
            <button class="btn btn-primary text-white">Save BREAD</button>
        </div>
    </form>
@endsection
