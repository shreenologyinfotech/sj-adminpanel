@extends('sjadminpanel::layouts.app')
@section('title', $bread->name . ' Details')
@section('page-title', $bread->name . ' Details')
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-end gap-2">
            <a href="{{ route('sjadmin.resources.index', $bread) }}" class="btn btn-light-secondary">Back</a>
            <a href="{{ route('sjadmin.resources.edit', [$bread, data_get($record, $primaryKey)]) }}" class="btn btn-primary text-white">Edit</a>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <tbody>
                @foreach ($fields as $field)
                    <tr>
                        <th style="width: 240px;">{{ $field['label'] }}</th>
                        <td>
                            @php
                                $value = data_get($record, $field['name']);
                                $decodedValue = is_string($value) ? json_decode($value, true) : $value;
                            @endphp
                            @if ($field['translatable'] ?? false)
                                {{ is_array($decodedValue) ? ($decodedValue[config('sjadminpanel.language.default')] ?? reset($decodedValue)) : $value }}
                            @elseif ($field['component'] === 'relationship' && ($field['relationship_type'] ?? 'belongsTo') === 'belongsToMany')
                                @forelse (data_get($record, $field['name'] . '__labels') ?? [] as $label)
                                    <span class="badge bg-light-secondary me-1">{{ $label }}</span>
                                @empty
                                    <span class="text-secondary">None</span>
                                @endforelse
                            @elseif ($field['component'] === 'relationship')
                                {{ data_get($record, $field['name'] . '__label') ?? $value }}
                            @elseif (in_array($field['component'], ['boolean', 'switch'], true))
                                <span class="badge bg-light-{{ $value ? 'success' : 'secondary' }}">{{ $value ? 'Yes' : 'No' }}</span>
                            @elseif ($field['component'] === 'image' && $value)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk(config('sjadminpanel.storage.disk'))->url($value) }}" alt="{{ $field['label'] }}" style="max-width: 220px;">
                            @elseif ($field['component'] === 'multiple_images' && is_array($decodedValue))
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($decodedValue as $image)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk(config('sjadminpanel.storage.disk'))->url($image) }}" alt="{{ $field['label'] }}" style="height: 92px;">
                                    @endforeach
                                </div>
                            @elseif ($field['component'] === 'file' && $value)
                                <a href="{{ \Illuminate\Support\Facades\Storage::disk(config('sjadminpanel.storage.disk'))->url($value) }}" target="_blank">{{ basename($value) }}</a>
                            @elseif (in_array($field['component'], ['checkbox', 'tags'], true) && is_array($decodedValue))
                                {{ implode(', ', $decodedValue) }}
                            @else
                                <pre class="mb-0 bg-transparent p-0 border-0">{{ $value }}</pre>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @foreach ($relatedLists ?? [] as $name => $related)
        <div class="card mt-3">
            <div class="card-header"><strong>{{ $related['field']['label'] }}</strong></div>
            <div class="card-body p-0">
                @if ($related['records']->isNotEmpty())
                    <table class="table mb-0">
                        <tbody>
                        @foreach ($related['records'] as $relatedRecord)
                            <tr>
                                <td>{{ data_get($relatedRecord, $related['field']['display_column'] ?: 'id') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-secondary p-3 mb-0">No related {{ strtolower($related['field']['label']) }} yet.</p>
                @endif
            </div>
        </div>
    @endforeach
@endsection
