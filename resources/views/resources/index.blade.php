@extends('sjadminpanel::layouts.app')
@section('title', $bread->name)
@section('page-title', $bread->name)
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search {{ strtolower($bread->name) }}..." value="{{ request('search') }}">
                <button class="btn btn-light-primary">Search</button>
            </form>
            <div class="d-flex gap-2">
                <a href="{{ route('sjadmin.resources.import.form', $bread) }}" class="btn btn-light-secondary">Import CSV</a>
                <a href="{{ route('sjadmin.resources.export', array_merge(['bread' => $bread], request()->query())) }}" class="btn btn-light-secondary">Export CSV</a>
                <a href="{{ route('sjadmin.resources.create', $bread) }}" class="btn btn-primary text-white">Add {{ $bread->name }}</a>
            </div>
        </div>

        <form id="bulk-form" method="POST" action="{{ route('sjadmin.resources.bulk-destroy', $bread) }}">
            @csrf @method('DELETE')

            <div class="px-3 py-2 border-bottom d-flex align-items-center gap-2" id="bulk-actions" style="display: none !important;">
                <span class="small text-secondary"><span id="bulk-count">0</span> selected</span>
                <button type="button" class="btn btn-sm btn-light-secondary" id="bulk-export">Export Selected</button>
                <button type="submit" class="btn btn-sm btn-light-danger" onclick="return confirm('Delete all selected records?')">Delete Selected</button>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 36px;"><input type="checkbox" id="select-all" class="form-check-input"></th>
                                @foreach ($fields as $field)
                                    <th>{{ $field['label'] }}</th>
                                @endforeach
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($records as $record)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ data_get($record, $primaryKey) }}" class="form-check-input row-check"></td>
                                @foreach ($fields as $field)
                                    <td class="text-truncate" style="max-width: 260px;">
                                        @php
                                            $value = data_get($record, $field['name']);
                                            $decodedValue = is_string($value) ? json_decode($value, true) : $value;
                                        @endphp
                                        @if ($field['translatable'] ?? false)
                                            {{ is_array($decodedValue) ? ($decodedValue[config('sjadminpanel.language.default')] ?? reset($decodedValue)) : $value }}
                                        @elseif ($field['component'] === 'relationship')
                                            {{ data_get($record, $field['name'] . '__label') ?? $value }}
                                        @elseif (in_array($field['component'], ['boolean', 'switch'], true))
                                            <span class="badge bg-light-{{ $value ? 'success' : 'secondary' }}">{{ $value ? 'Yes' : 'No' }}</span>
                                        @elseif ($field['component'] === 'image' && $value)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::disk(config('sjadminpanel.storage.disk'))->url($value) }}" alt="{{ $field['label'] }}" style="height: 42px;">
                                        @elseif ($field['component'] === 'multiple_images' && is_array($decodedValue) && count($decodedValue))
                                            <span class="badge bg-light-primary">{{ count($decodedValue) }} images</span>
                                        @elseif (in_array($field['component'], ['checkbox', 'tags'], true) && is_array($decodedValue))
                                            {{ implode(', ', $decodedValue) }}
                                        @else
                                            {{ $value }}
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-end">
                                    <a href="{{ route('sjadmin.resources.show', [$bread, data_get($record, $primaryKey)]) }}" class="btn btn-sm btn-light-primary">View</a>
                                    <a href="{{ route('sjadmin.resources.edit', [$bread, data_get($record, $primaryKey)]) }}" class="btn btn-sm btn-light-secondary">Edit</a>
                                    <form action="{{ route('sjadmin.resources.destroy', [$bread, data_get($record, $primaryKey)]) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light-danger" onclick="return confirm('Delete this record?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $fields->count() + 2 }}" class="text-center text-muted py-4">No records found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>

        <div class="card-footer">{{ $records->links() }}</div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const selectAll = document.getElementById('select-all');
            const bulkActions = document.getElementById('bulk-actions');
            const bulkCount = document.getElementById('bulk-count');
            const bulkExport = document.getElementById('bulk-export');

            function checks() {
                return Array.from(document.querySelectorAll('.row-check'));
            }

            function refresh() {
                const checked = checks().filter((c) => c.checked);
                bulkCount.textContent = checked.length;
                bulkActions.style.setProperty('display', checked.length ? 'flex' : 'none', 'important');
            }

            selectAll?.addEventListener('change', function () {
                checks().forEach((c) => { c.checked = selectAll.checked; });
                refresh();
            });

            checks().forEach((c) => c.addEventListener('change', refresh));

            bulkExport?.addEventListener('click', function () {
                const ids = checks().filter((c) => c.checked).map((c) => c.value);
                const params = new URLSearchParams(window.location.search);
                ids.forEach((id) => params.append('ids[]', id));
                window.location.href = '{{ route('sjadmin.resources.export', $bread) }}?' + params.toString();
            });
        })();
    </script>
@endpush
