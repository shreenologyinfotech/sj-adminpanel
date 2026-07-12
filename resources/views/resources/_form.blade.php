@foreach ($fields as $field)
    @php
        $component = $field['component'] ?? 'text';
        $rawValue = old($field['name'], $record ? data_get($record, $field['name']) : ($field['default'] ?? null));
        $decodedValue = is_string($rawValue) ? json_decode($rawValue, true) : $rawValue;
        $selectedValues = is_array($decodedValue) ? $decodedValue : [];
        $options = collect(preg_split('/\r\n|\r|\n/', (string) ($field['options'] ?? '')))
            ->map(fn ($option) => trim($option))
            ->filter()
            ->mapWithKeys(function ($option) {
                foreach (['=>', '|', '='] as $separator) {
                    if (str_contains($option, $separator)) {
                        [$value, $label] = array_map('trim', explode($separator, $option, 2));

                        return [$value => $label];
                    }
                }

                return [$option => $option];
            });
        $placeholder = $field['placeholder'] ?? null;
    @endphp

    @if (($field['hidden'] ?? false) || $component === 'hidden')
        <input type="hidden" name="{{ $field['name'] }}" value="{{ $rawValue }}">
        @continue
    @endif

    @if ($field['translatable'] ?? false)
        @php
            $locales = config('sjadminpanel.language.available', ['en']);
            $existingTranslations = $record ? data_get($record, $field['name']) : null;
            $decodedTranslations = is_string($existingTranslations) ? (json_decode($existingTranslations, true) ?: []) : (array) $existingTranslations;
            $tabId = 'translatable-' . $field['name'];
        @endphp
        <div class="mb-3">
            <label class="form-label">
                {{ $field['label'] }}
                @if ($field['required'])
                    <span class="text-danger">*</span>
                @endif
            </label>

            @if (count($locales) > 1)
                <ul class="nav nav-tabs mb-2">
                    @foreach ($locales as $i => $locale)
                        <li class="nav-item">
                            <button type="button" class="nav-link @if($i === 0) active @endif" data-bs-toggle="tab" data-bs-target="#{{ $tabId }}-{{ $locale }}">{{ strtoupper($locale) }}</button>
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="tab-content">
                @foreach ($locales as $i => $locale)
                    <div class="tab-pane @if($i === 0) show active @endif" id="{{ $tabId }}-{{ $locale }}">
                        @if (in_array($component, ['textarea', 'editor', 'markdown'], true))
                            <textarea name="{{ $field['name'] }}[{{ $locale }}]" class="form-control" rows="5"
                                      @required($field['required'] && $locale === config('sjadminpanel.language.default'))>{{ old($field['name'] . '.' . $locale, $decodedTranslations[$locale] ?? '') }}</textarea>
                        @else
                            <input type="text" name="{{ $field['name'] }}[{{ $locale }}]" class="form-control"
                                   value="{{ old($field['name'] . '.' . $locale, $decodedTranslations[$locale] ?? '') }}"
                                   @required($field['required'] && $locale === config('sjadminpanel.language.default'))>
                        @endif
                    </div>
                @endforeach
            </div>

            @if (! blank($field['help_text'] ?? null))
                <div class="form-text">{{ $field['help_text'] }}</div>
            @endif
            @error($field['name'] . '.' . config('sjadminpanel.language.default'))
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        @continue
    @endif

    <div class="mb-3">
        <label class="form-label">
            {{ $field['label'] }}
            @if ($field['required'])
                <span class="text-danger">*</span>
            @endif
        </label>

        @switch($component)
            @case('textarea')
            @case('editor')
            @case('markdown')
                <textarea name="{{ $field['name'] }}" class="form-control" rows="5" placeholder="{{ $placeholder }}" @required($field['required'])>{{ $rawValue }}</textarea>
                @break

            @case('code')
            @case('json')
                <textarea name="{{ $field['name'] }}" class="form-control font-monospace" rows="7" placeholder="{{ $placeholder }}" @required($field['required'])>{{ $rawValue }}</textarea>
                @break

            @case('boolean')
            @case('switch')
                <div class="form-check form-switch">
                    <input type="hidden" name="{{ $field['name'] }}" value="0">
                    <input type="checkbox" name="{{ $field['name'] }}" value="1" class="form-check-input" @checked((bool) $rawValue)>
                </div>
                @break

            @case('select')
                <select name="{{ $field['name'] }}" class="form-select" @required($field['required'])>
                    <option value="">Select...</option>
                    @foreach ($options as $optionValue => $optionLabel)
                        <option value="{{ $optionValue }}" @selected((string) $rawValue === (string) $optionValue)>{{ $optionLabel }}</option>
                    @endforeach
                </select>
                @break

            @case('relationship')
                @if (($field['relationship_type'] ?? 'belongsTo') === 'belongsToMany')
                    <select name="{{ $field['name'] }}[]" class="form-select js-relationship-select" multiple
                            @if (($relationshipOptions[$field['name'] . '__count'] ?? 0) > 500)
                                data-ajax-url="{{ route('sjadmin.resources.relationship-search', [$bread, $field['name']]) }}"
                            @endif
                            @required($field['required'])>
                        @foreach (($relationshipOptions[$field['name']] ?? []) as $optionValue => $optionLabel)
                            <option value="{{ $optionValue }}" @selected(in_array((string) $optionValue, array_map('strval', $selectedValues), true))>{{ $optionLabel }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Search to find options if the list is long.</div>
                @else
                    <select name="{{ $field['name'] }}" class="form-select js-relationship-select"
                            @if (($relationshipOptions[$field['name'] . '__count'] ?? 0) > 500)
                                data-ajax-url="{{ route('sjadmin.resources.relationship-search', [$bread, $field['name']]) }}"
                            @endif
                            @required($field['required'])>
                        <option value="">Select...</option>
                        @foreach (($relationshipOptions[$field['name']] ?? []) as $optionValue => $optionLabel)
                            <option value="{{ $optionValue }}" @selected((string) $rawValue === (string) $optionValue)>{{ $optionLabel }}</option>
                        @endforeach
                    </select>
                @endif
                @if (empty($relationshipOptions[$field['name']] ?? null))
                    <div class="form-text text-warning">No options found — check that "related_model" and "display_column" are configured correctly for this field.</div>
                @endif
                @break

            @case('radio')
                <div class="d-flex flex-wrap gap-3">
                    @foreach ($options as $optionValue => $optionLabel)
                        <label class="form-check">
                            <input type="radio" name="{{ $field['name'] }}" value="{{ $optionValue }}" class="form-check-input" @checked((string) $rawValue === (string) $optionValue) @required($field['required'])>
                            <span class="form-check-label">{{ $optionLabel }}</span>
                        </label>
                    @endforeach
                </div>
                @break

            @case('checkbox')
                @if ($options->isNotEmpty())
                    <div class="d-flex flex-wrap gap-3">
                        @foreach ($options as $optionValue => $optionLabel)
                            <label class="form-check">
                                <input type="checkbox" name="{{ $field['name'] }}[]" value="{{ $optionValue }}" class="form-check-input" @checked(in_array((string) $optionValue, array_map('strval', $selectedValues), true))>
                                <span class="form-check-label">{{ $optionLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="form-check">
                        <input type="hidden" name="{{ $field['name'] }}" value="0">
                        <input type="checkbox" name="{{ $field['name'] }}" value="1" class="form-check-input" @checked((bool) $rawValue)>
                    </div>
                @endif
                @break

            @case('file')
            @case('image')
                @if ($rawValue)
                    <div class="mb-2">
                        @if ($component === 'image')
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk(config('sjadminpanel.storage.disk'))->url($rawValue) }}" alt="{{ $field['label'] }}" style="max-height: 90px;">
                        @else
                            <code>{{ $rawValue }}</code>
                        @endif
                    </div>
                @endif
                <input type="file" name="{{ $field['name'] }}" class="form-control" @if($component === 'image') accept="image/*" @endif @required($field['required'] && ! $rawValue)>
                @break

            @case('multiple_images')
                @if ($selectedValues)
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        @foreach ($selectedValues as $image)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk(config('sjadminpanel.storage.disk'))->url($image) }}" alt="{{ $field['label'] }}" style="height: 72px;">
                        @endforeach
                    </div>
                @endif
                <input type="file" name="{{ $field['name'] }}[]" class="form-control" accept="image/*" multiple @required($field['required'] && ! $selectedValues)>
                @break

            @case('number')
                <input type="number" name="{{ $field['name'] }}" value="{{ $rawValue }}" class="form-control" placeholder="{{ $placeholder }}" @required($field['required'])>
                @break

            @case('email')
                <input type="email" name="{{ $field['name'] }}" value="{{ $rawValue }}" class="form-control" placeholder="{{ $placeholder }}" @required($field['required'])>
                @break

            @case('password')
                <input type="password" name="{{ $field['name'] }}" class="form-control" placeholder="{{ $record ? 'Leave blank to keep current password' : $placeholder }}" @required($field['required'] && ! $record)>
                @break

            @case('slug')
                <input type="text" name="{{ $field['name'] }}" value="{{ $rawValue }}" class="form-control" placeholder="{{ $placeholder ?: 'Auto-generated if blank' }}" @required($field['required'])>
                @break

            @case('color')
                <input type="color" name="{{ $field['name'] }}" value="{{ $rawValue ?: '#000000' }}" class="form-control form-control-color" @required($field['required'])>
                @break

            @case('tags')
                <input type="text" name="{{ $field['name'] }}" value="{{ $selectedValues ? implode(', ', $selectedValues) : $rawValue }}" class="form-control" placeholder="{{ $placeholder ?: 'tag-one, tag-two' }}" @required($field['required'])>
                @break

            @case('date')
                <input type="date" name="{{ $field['name'] }}" value="{{ $rawValue }}" class="form-control" @required($field['required'])>
                @break

            @case('datetime')
                <input type="datetime-local" name="{{ $field['name'] }}" value="{{ $rawValue ? \Illuminate\Support\Carbon::parse($rawValue)->format('Y-m-d\TH:i') : '' }}" class="form-control" @required($field['required'])>
                @break

            @case('time')
                <input type="time" name="{{ $field['name'] }}" value="{{ $rawValue }}" class="form-control" @required($field['required'])>
                @break

            @default
                <input type="text" name="{{ $field['name'] }}" value="{{ $rawValue }}" class="form-control" placeholder="{{ $placeholder }}" @required($field['required'])>
        @endswitch

        @if (! blank($field['help_text'] ?? null))
            <div class="form-text">{{ $field['help_text'] }}</div>
        @endif

        @error($field['name'])
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
@endforeach
