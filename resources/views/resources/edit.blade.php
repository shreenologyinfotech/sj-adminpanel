@extends('sjadminpanel::layouts.app')
@section('title', 'Edit ' . $bread->name)
@section('page-title', 'Edit ' . $bread->name)

@push('styles')
    <link href="{{ asset('vendor/sjadminpanel/vendor/select/select2.min.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('sjadmin.resources.update', [$bread, data_get($record, $primaryKey)]) }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                @include('sjadminpanel::resources._form', ['fields' => $fields, 'record' => $record, 'relationshipOptions' => $relationshipOptions])
                <div class="text-end">
                    <a href="{{ route('sjadmin.resources.show', [$bread, data_get($record, $primaryKey)]) }}" class="btn btn-light-secondary">Cancel</a>
                    <button class="btn btn-primary text-white">Save</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/sjadminpanel/vendor/select/select2.min.js') }}"></script>
    <script>
        $(function () {
            $('.js-relationship-select').each(function () {
                const $el = $(this);
                const options = { width: '100%', placeholder: 'Select...', allowClear: ! $el.prop('multiple') };

                if ($el.data('ajax-url')) {
                    options.ajax = {
                        url: $el.data('ajax-url'),
                        dataType: 'json',
                        delay: 250,
                        data: (params) => ({ q: params.term }),
                        processResults: (data) => ({ results: data }),
                    };
                    options.minimumInputLength = 1;
                }

                $el.select2(options);
            });
        });
    </script>
@endpush
