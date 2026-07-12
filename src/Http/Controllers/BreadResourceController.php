<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Safarjaisur\AdminPanel\Models\ActivityLog;
use Safarjaisur\AdminPanel\Models\Bread;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BreadResourceController extends Controller
{
    public function index(Request $request, Bread $bread): View
    {
        $fields = $this->fieldsFor($bread, 'browse');
        $records = $this->records($request, $bread);

        $this->hydrateRelationshipLabels($fields, collect($records->items()));

        return view('sjadminpanel::resources.index', [
            'bread' => $bread,
            'fields' => $fields,
            'records' => $records,
            'primaryKey' => $this->primaryKey($bread),
        ]);
    }

    /**
     * Streams every browsable column for every record as a CSV download.
     * Relationship columns export their resolved display label rather
     * than the raw foreign key so the file is useful outside the panel.
     */
    public function export(Request $request, Bread $bread): StreamedResponse
    {
        $fields = $this->fieldsFor($bread, 'browse');
        $primaryKey = $this->primaryKey($bread);

        $query = DB::table($bread->table_name)->orderByDesc($primaryKey);

        if ($request->filled('ids')) {
            $query->whereIn($primaryKey, (array) $request->input('ids'));
        }

        $rows = collect($query->get());

        $this->hydrateRelationshipLabels($fields, $rows);

        $filename = $bread->slug . '-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($fields, $rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $fields->pluck('label')->all());

            foreach ($rows as $row) {
                fputcsv($handle, $fields->map(fn (array $field) => $this->exportValue($field, $row))->all());
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Deletes every record whose id was checked in the index bulk-select.
     */
    public function bulkDestroy(Request $request, Bread $bread): RedirectResponse
    {
        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['required']]);

        $count = DB::table($bread->table_name)->whereIn($this->primaryKey($bread), $request->input('ids'))->delete();

        $this->logActivity('bread.bulk_deleted', $bread, implode(',', $request->input('ids')));

        return redirect()->route('sjadmin.resources.index', $bread)->with('success', "{$count} record(s) deleted.");
    }

    public function importForm(Bread $bread): View
    {
        return view('sjadminpanel::resources.import', [
            'bread' => $bread,
            'fields' => $this->fieldsFor($bread, 'add'),
        ]);
    }

    /**
     * Imports a CSV whose header row names match BREAD field names
     * (see importForm for the exact list). Relationship, file/image, and
     * password fields are skipped — they need a real upload UI or a
     * resolvable foreign key, neither of which a flat CSV can safely provide.
     */
    public function import(Request $request, Bread $bread): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:10240']]);

        $importableFields = $this->fieldsFor($bread, 'add')
            ->reject(fn (array $field): bool => in_array($field['component'], ['relationship', 'file', 'image', 'multiple_images', 'password'], true))
            ->keyBy('name');

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle);

        abort_unless($header, 422, 'The uploaded file appears to be empty.');

        $columns = Schema::getColumnListing($bread->table_name);
        $hasTimestamps = in_array('created_at', $columns, true);

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($handle, $header, $importableFields, $bread, $hasTimestamps, &$created, &$skipped): void {
            while (($row = fgetcsv($handle)) !== false) {
                $rowData = array_combine($header, array_pad($row, count($header), null));
                $data = [];

                foreach ($importableFields as $name => $field) {
                    if (array_key_exists($name, $rowData)) {
                        $data[$name] = blank($rowData[$name]) ? null : $rowData[$name];
                    }
                }

                if (empty($data)) {
                    $skipped++;

                    continue;
                }

                if ($hasTimestamps) {
                    $data['created_at'] = $data['updated_at'] = now();
                }

                DB::table($bread->table_name)->insert($data);
                $created++;
            }
        });

        fclose($handle);

        $this->logActivity('bread.imported', $bread, "{$created} created, {$skipped} skipped");

        return redirect()
            ->route('sjadmin.resources.index', $bread)
            ->with('success', "Import complete: {$created} record(s) created, {$skipped} row(s) skipped.");
    }

    public function create(Bread $bread): View
    {
        $fields = $this->fieldsFor($bread, 'add');

        return view('sjadminpanel::resources.create', [
            'bread' => $bread,
            'fields' => $fields,
            'relationshipOptions' => $this->relationshipOptionsFor($fields),
            'record' => null,
        ]);
    }

    public function store(Request $request, Bread $bread): RedirectResponse
    {
        $data = $this->validatedData($request, $bread, 'add');
        $columns = Schema::getColumnListing($bread->table_name);

        if (in_array('created_at', $columns, true)) {
            $data['created_at'] = now();
        }

        if (in_array('updated_at', $columns, true)) {
            $data['updated_at'] = now();
        }

        $id = DB::table($bread->table_name)->insertGetId($data);
        $this->syncPivotRelationships($request, $bread, 'add', $id);
        $this->logActivity('bread.created', $bread, (string) $id);

        return redirect()
            ->route('sjadmin.resources.show', [$bread, $id])
            ->with('success', "{$bread->name} record created.");
    }

    public function show(Bread $bread, string $record): View
    {
        $allFields = $this->fieldsFor($bread, 'read');
        $fields = $allFields->reject(fn (array $field): bool => $field['component'] === 'relationship' && $field['relationship_type'] === 'hasMany');
        $hasManyFields = $allFields->filter(fn (array $field): bool => $field['component'] === 'relationship' && $field['relationship_type'] === 'hasMany');

        $row = $this->findRecord($bread, $record);

        $this->hydrateRelationshipLabels($fields, collect([$row]));
        $this->hydratePivotLabels($fields, $row);

        return view('sjadminpanel::resources.show', [
            'bread' => $bread,
            'fields' => $fields,
            'record' => $row,
            'primaryKey' => $this->primaryKey($bread),
            'relatedLists' => $this->relatedListingsFor($hasManyFields, $row),
        ]);
    }

    public function edit(Bread $bread, string $record): View
    {
        $fields = $this->fieldsFor($bread, 'edit');
        $row = $this->findRecord($bread, $record);

        $this->hydratePivotSelections($fields, $row);

        return view('sjadminpanel::resources.edit', [
            'bread' => $bread,
            'fields' => $fields,
            'relationshipOptions' => $this->relationshipOptionsFor($fields),
            'record' => $row,
            'primaryKey' => $this->primaryKey($bread),
        ]);
    }

    public function update(Request $request, Bread $bread, string $record): RedirectResponse
    {
        $primaryKey = $this->primaryKey($bread);
        $data = $this->validatedData($request, $bread, 'edit', $record);
        $columns = Schema::getColumnListing($bread->table_name);

        if (in_array('updated_at', $columns, true)) {
            $data['updated_at'] = now();
        }

        DB::table($bread->table_name)->where($primaryKey, $record)->update($data);
        $this->syncPivotRelationships($request, $bread, 'edit', $record);
        $this->logActivity('bread.updated', $bread, $record);

        return redirect()
            ->route('sjadmin.resources.show', [$bread, $record])
            ->with('success', "{$bread->name} record updated.");
    }

    public function destroy(Bread $bread, string $record): RedirectResponse
    {
        DB::table($bread->table_name)->where($this->primaryKey($bread), $record)->delete();
        $this->logActivity('bread.deleted', $bread, $record);

        return redirect()
            ->route('sjadmin.resources.index', $bread)
            ->with('success', "{$bread->name} record deleted.");
    }

    protected function records(Request $request, Bread $bread): LengthAwarePaginator
    {
        $query = DB::table($bread->table_name);
        $primaryKey = $this->primaryKey($bread);

        if ($request->filled('search')) {
            $search = '%' . $request->string('search') . '%';
            $searchable = $this->fieldsFor($bread, 'browse')
                ->filter(fn (array $field): bool => in_array($field['component'], ['text', 'textarea', 'select', 'radio', 'email', 'slug', 'tags'], true));

            $relationshipMatches = $this->fieldsFor($bread, 'browse')
                ->filter(fn (array $field): bool => $field['component'] === 'relationship'
                    && ($field['relationship_type'] ?? 'belongsTo') === 'belongsTo'
                    && filled($field['related_model'] ?? null));

            $query->where(function ($query) use ($searchable, $relationshipMatches, $search): void {
                foreach ($searchable as $field) {
                    $query->orWhere($field['name'], 'like', $search);
                }

                foreach ($relationshipMatches as $field) {
                    $table = (string) $field['related_model'];
                    $displayColumn = (string) ($field['display_column'] ?: 'name');
                    $valueColumn = (string) ($field['value_column'] ?: 'id');

                    if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $displayColumn)) {
                        continue;
                    }

                    $matchingIds = DB::table($table)->where($displayColumn, 'like', $search)->pluck($valueColumn);

                    if ($matchingIds->isNotEmpty()) {
                        $query->orWhereIn($field['name'], $matchingIds);
                    }
                }
            });
        }

        if (in_array($primaryKey, Schema::getColumnListing($bread->table_name), true)) {
            $query->orderByDesc($primaryKey);
        }

        return $query->paginate(config('sjadminpanel.pagination.per_page'))->withQueryString();
    }

    protected function findRecord(Bread $bread, string $record): object
    {
        $row = DB::table($bread->table_name)->where($this->primaryKey($bread), $record)->first();

        abort_unless($row, 404);

        return $row;
    }

    protected function validatedData(Request $request, Bread $bread, string $ability, ?string $record = null): array
    {
        $fields = $this->fieldsFor($bread, $ability);
        $rules = [];
        $existingRecord = $record ? $this->findRecord($bread, $record) : null;

        foreach ($fields as $field) {
            $component = $field['component'] ?? 'text';

            if ($field['translatable'] ?? false) {
                $defaultLocale = config('sjadminpanel.language.default', 'en');
                $rules[$field['name'] . '.' . $defaultLocale] = ($field['required'] ?? false) ? ['required'] : ['nullable'];

                continue;
            }

            $fieldRules = array_filter(explode('|', (string) ($field['validation'] ?? '')));
            $hasExistingUpload = $existingRecord
                && in_array($component, ['file', 'image', 'multiple_images'], true)
                && filled(data_get($existingRecord, $field['name']));
            $optionalOnUpdate = $existingRecord
                && ($component === 'password' || $hasExistingUpload);

            if (($field['required'] ?? false) && ! $optionalOnUpdate) {
                array_unshift($fieldRules, 'required');
            } elseif ($field['nullable'] ?? true) {
                array_unshift($fieldRules, 'nullable');
            }

            if ($field['unique'] ?? false) {
                $fieldRules[] = Rule::unique($bread->table_name, $field['name'])
                    ->ignore($record, $this->primaryKey($bread));
            }

            if ($component === 'email') {
                $fieldRules[] = 'email';
            }

            if ($component === 'number') {
                $fieldRules[] = 'numeric';
            }

            if ($component === 'image') {
                $fieldRules[] = 'image';
            }

            if ($component === 'file') {
                $fieldRules[] = 'file';
            }

            if ($component === 'multiple_images') {
                $rules[$field['name'] . '.*'] = ['image'];
                $fieldRules[] = 'array';
            }

            if ($component === 'relationship' && ($field['relationship_type'] ?? 'belongsTo') === 'belongsToMany' && filled($field['related_model'] ?? null)) {
                $fieldRules = [($field['required'] ?? false) ? 'required' : 'nullable', 'array'];
                $rules[$field['name'] . '.*'] = [Rule::exists((string) $field['related_model'], (string) ($field['value_column'] ?: 'id'))];
            } elseif ($component === 'relationship' && filled($field['related_model'] ?? null)) {
                $fieldRules[] = Rule::exists((string) $field['related_model'], (string) ($field['value_column'] ?: 'id'));
            }

            $rules[$field['name']] = $fieldRules ?: ['nullable'];
        }

        $request->validate($rules);

        return $fields
            ->mapWithKeys(fn (array $field): array => [$field['name'] => $this->inputValue($request, $bread, $field)])
            ->filter(fn (mixed $value): bool => $value !== '__sjadmin_skip__')
            ->all();
    }

    protected function inputValue(Request $request, Bread $bread, array $field): mixed
    {
        $name = $field['name'];
        $component = $field['component'] ?? 'text';

        if ($field['translatable'] ?? false) {
            $locales = config('sjadminpanel.language.available', ['en']);
            $submitted = (array) $request->input($name, []);

            $translations = collect($locales)
                ->mapWithKeys(fn (string $locale): array => [$locale => (string) ($submitted[$locale] ?? '')])
                ->all();

            return json_encode($translations);
        }

        if (in_array($component, ['boolean', 'switch'], true)) {
            return $request->boolean($name);
        }

        if ($component === 'checkbox') {
            $value = $request->input($name);

            return is_array($value) ? json_encode(array_values($value)) : $request->boolean($name);
        }

        if (in_array($component, ['file', 'image'], true)) {
            if (! $request->hasFile($name)) {
                return '__sjadmin_skip__';
            }

            return $request->file($name)->store('bread/' . $bread->slug, config('sjadminpanel.storage.disk'));
        }

        if ($component === 'multiple_images') {
            if (! $request->hasFile($name)) {
                return '__sjadmin_skip__';
            }

            $paths = collect($request->file($name))
                ->map(fn ($file): string => $file->store('bread/' . $bread->slug, config('sjadminpanel.storage.disk')))
                ->values()
                ->all();

            return json_encode($paths);
        }

        if ($component === 'password') {
            $value = $request->input($name);

            return blank($value) ? '__sjadmin_skip__' : Hash::make((string) $value);
        }

        if ($component === 'tags') {
            $value = $request->input($name);
            $tags = is_array($value)
                ? $value
                : collect(explode(',', (string) $value))->map(fn ($tag) => trim($tag))->filter()->values()->all();

            return json_encode($tags);
        }

        if ($component === 'slug' || ($field['slug'] ?? false)) {
            $value = $request->input($name);

            if (blank($value) && filled($field['slug_from'] ?? null)) {
                $value = $request->input((string) $field['slug_from']);
            }

            return blank($value) ? null : Str::slug((string) $value);
        }

        if (in_array($component, ['json', 'code'], true)) {
            $value = $request->input($name);

            return blank($value) ? null : $value;
        }

        if ($component === 'hidden') {
            return $request->input($name, $field['default'] ?? null);
        }

        if ($component === 'relationship' && ($field['relationship_type'] ?? 'belongsTo') === 'belongsToMany') {
            return '__sjadmin_skip__';
        }

        if ($component === 'relationship') {
            $value = $request->input($name);

            return blank($value) ? null : $value;
        }

        return $request->input($name);
    }

    protected function fieldsFor(Bread $bread, string $ability): Collection
    {
        return collect($bread->fields ?? [])
            ->map(fn (array $field): array => $this->normalizeField($field))
            ->filter(fn (array $field): bool => (bool) ($field[$ability] ?? false))
            // hasMany is a read-only related listing (rendered separately on the
            // show page), never a real column or an editable form input.
            ->when(in_array($ability, ['add', 'edit', 'browse'], true), fn (Collection $fields) => $fields->reject(
                fn (array $field): bool => $field['component'] === 'relationship' && $field['relationship_type'] === 'hasMany'
            ))
            ->sortBy('order')
            ->values();
    }

    protected function normalizeField(array $field): array
    {
        $name = (string) ($field['name'] ?? '');
        $type = (string) ($field['type'] ?? $field['type_name'] ?? 'string');
        $component = (string) ($field['component'] ?? $this->componentForType($type, $name));

        return array_merge([
            'name' => $name,
            'label' => str($name)->headline()->value(),
            'type' => $type,
            'type_name' => $field['type_name'] ?? $type,
            'component' => $component,
            'order' => (int) ($field['order'] ?? 0),
            'nullable' => (bool) ($field['nullable'] ?? true),
            'required' => (bool) ($field['required'] ?? false),
            'unique' => (bool) ($field['unique'] ?? false),
            'default' => $field['default'] ?? null,
            'validation' => $field['validation'] ?? null,
            'options' => $field['options'] ?? null,
            'placeholder' => $field['placeholder'] ?? null,
            'help_text' => $field['help_text'] ?? null,
            'width' => $field['width'] ?? 'col-md-12',
            'tab' => $field['tab'] ?? null,
            'panel' => $field['panel'] ?? null,
            'hidden' => (bool) ($field['hidden'] ?? false),
            'slug' => (bool) ($field['slug'] ?? false),
            'slug_from' => $field['slug_from'] ?? null,
            'translatable' => (bool) ($field['translatable'] ?? false),
            'relationship' => (bool) ($field['relationship'] ?? false),
            'relationship_type' => $field['relationship_type'] ?? 'belongsTo',
            'related_model' => $field['related_model'] ?? null,
            'value_column' => $field['value_column'] ?? 'id',
            'display_column' => $field['display_column'] ?? 'name',
            'pivot_table' => $field['pivot_table'] ?? null,
            'pivot_local_key' => $field['pivot_local_key'] ?? null,
            'pivot_related_key' => $field['pivot_related_key'] ?? null,
            'foreign_key' => $field['foreign_key'] ?? null,
            'browse' => (bool) ($field['browse'] ?? true),
            'read' => (bool) ($field['read'] ?? true),
            'edit' => (bool) ($field['edit'] ?? true),
            'add' => (bool) ($field['add'] ?? true),
        ], $field);
    }

    protected function componentForType(string $type, string $name = ''): string
    {
        $type = strtolower($type);
        $name = strtolower($name);

        return match (true) {
            $name === 'password' => 'password',
            str_contains($name, 'email') => 'email',
            str_contains($name, 'slug') => 'slug',
            str_contains($type, 'text') => 'textarea',
            str_contains($type, 'int'), str_contains($type, 'decimal'), str_contains($type, 'float'), str_contains($type, 'double') => 'number',
            str_contains($type, 'bool'), str_contains($type, 'tinyint(1)') => 'boolean',
            str_contains($type, 'date') && str_contains($type, 'time') => 'datetime',
            str_contains($type, 'date') => 'date',
            str_contains($type, 'time') => 'time',
            str_contains($type, 'json') => 'json',
            default => 'text',
        };
    }

    protected function primaryKey(Bread $bread): string
    {
        $columns = Schema::getColumnListing($bread->table_name);

        return in_array('id', $columns, true) ? 'id' : ($columns[0] ?? 'id');
    }

    protected function displayValue(Bread $bread, array $field, object $record): string
    {
        $value = data_get($record, $field['name']);

        if ($value === null || $value === '') {
            return '';
        }

        if (in_array($field['component'], ['file', 'image'], true)) {
            return Storage::disk(config('sjadminpanel.storage.disk'))->url((string) $value);
        }

        return (string) $value;
    }

    /**
     * value_column => display_column options for every relationship field,
     * keyed by field name, for populating <select> dropdowns in the form.
     *
     * @param Collection<int, array> $fields
     * @return array<string, Collection<int|string, string>>
     */
    /**
     * JSON search endpoint backing the select2 AJAX mode for relationship
     * fields whose related table is too large to load in full (> 500 rows).
     *
     * @return array<int, array{id: mixed, text: string}>
     */
    public function relationshipSearch(Request $request, Bread $bread, string $field): \Illuminate\Http\JsonResponse
    {
        $fieldConfig = $this->fieldsFor($bread, 'edit')
            ->merge($this->fieldsFor($bread, 'add'))
            ->first(fn (array $candidate): bool => $candidate['name'] === $field && $candidate['component'] === 'relationship');

        abort_unless($fieldConfig, 404);

        $table = (string) $fieldConfig['related_model'];
        $valueColumn = (string) ($fieldConfig['value_column'] ?: 'id');
        $displayColumn = (string) ($fieldConfig['display_column'] ?: 'name');

        abort_unless(Schema::hasTable($table) && Schema::hasColumn($table, $displayColumn), 404);

        $results = DB::table($table)
            ->select([$valueColumn, $displayColumn])
            ->when($request->filled('q'), fn ($q) => $q->where($displayColumn, 'like', '%' . $request->string('q') . '%'))
            ->orderBy($displayColumn)
            ->limit(50)
            ->get()
            ->map(fn ($row) => ['id' => data_get($row, $valueColumn), 'text' => (string) data_get($row, $displayColumn)]);

        return response()->json($results);
    }

    protected function relationshipOptionsFor(Collection $fields): array
    {
        return $fields
            ->filter(fn (array $field): bool => $field['component'] === 'relationship' && filled($field['related_model'] ?? null))
            ->flatMap(function (array $field): array {
                $table = (string) $field['related_model'];
                $valueColumn = (string) ($field['value_column'] ?: 'id');
                $displayColumn = (string) ($field['display_column'] ?: 'name');

                if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $displayColumn)) {
                    return [$field['name'] => collect(), $field['name'] . '__count' => 0];
                }

                $options = DB::table($table)
                    ->select([$valueColumn, $displayColumn])
                    ->orderBy($displayColumn)
                    ->limit(500)
                    ->get()
                    ->mapWithKeys(fn ($row) => [data_get($row, $valueColumn) => (string) data_get($row, $displayColumn)]);

                return [
                    $field['name'] => $options,
                    $field['name'] . '__count' => DB::table($table)->count(),
                ];
            })
            ->all();
    }

    /**
     * Attaches a "{field}__label" attribute to every record for each
     * relationship field, resolved from the related table in a single
     * query per field (never N+1 per row).
     *
     * @param Collection<int, array> $fields
     * @param Collection<int, object> $records
     */
    protected function hydrateRelationshipLabels(Collection $fields, Collection $records): void
    {
        $relationshipFields = $fields->filter(fn (array $field): bool => $field['component'] === 'relationship' && filled($field['related_model'] ?? null));

        foreach ($relationshipFields as $field) {
            $table = (string) $field['related_model'];
            $valueColumn = (string) ($field['value_column'] ?: 'id');
            $displayColumn = (string) ($field['display_column'] ?: 'name');
            $name = $field['name'];

            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $displayColumn)) {
                continue;
            }

            $ids = $records->map(fn ($record) => data_get($record, $name))->filter()->unique()->values();

            if ($ids->isEmpty()) {
                continue;
            }

            $labels = DB::table($table)
                ->whereIn($valueColumn, $ids)
                ->pluck($displayColumn, $valueColumn);

            foreach ($records as $record) {
                $record->{$name . '__label'} = $labels->get(data_get($record, $name));
            }
        }
    }

    /**
     * Replaces the linked rows in a belongsToMany field's pivot table to
     * match the submitted selection. hasMany/belongsTo fields are ignored
     * here — belongsTo already lives on the main record's column, and
     * hasMany is read-only.
     */
    protected function syncPivotRelationships(Request $request, Bread $bread, string $ability, int|string $recordId): void
    {
        $pivotFields = $this->fieldsFor($bread, $ability)
            ->filter(fn (array $field): bool => $field['component'] === 'relationship'
                && ($field['relationship_type'] ?? 'belongsTo') === 'belongsToMany'
                && filled($field['pivot_table'] ?? null));

        foreach ($pivotFields as $field) {
            [$pivotTable, $localKey, $relatedKey] = $this->pivotKeys($bread, $field);

            $selectedIds = collect($request->input($field['name'], []))->filter()->values();

            DB::table($pivotTable)->where($localKey, $recordId)->delete();

            if ($selectedIds->isNotEmpty()) {
                DB::table($pivotTable)->insert(
                    $selectedIds->map(fn ($relatedId): array => [$localKey => $recordId, $relatedKey => $relatedId])->all()
                );
            }
        }
    }

    /**
     * Attaches each belongsToMany field's currently-linked ids onto the
     * record (as a plain array) so the edit form can pre-select them.
     *
     * @param Collection<int, array> $fields
     */
    /**
     * Like hydratePivotSelections, but resolves display labels (for the
     * read-only show page) instead of raw ids (for the editable form).
     *
     * @param Collection<int, array> $fields
     */
    protected function hydratePivotLabels(Collection $fields, object $record): void
    {
        foreach ($fields as $field) {
            if ($field['component'] !== 'relationship' || ($field['relationship_type'] ?? 'belongsTo') !== 'belongsToMany' || blank($field['pivot_table'] ?? null)) {
                continue;
            }

            [$pivotTable, $localKey, $relatedKey] = $this->pivotKeys(null, $field);
            $table = (string) $field['related_model'];
            $displayColumn = (string) ($field['display_column'] ?: 'name');

            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $displayColumn)) {
                continue;
            }

            $relatedIds = DB::table($pivotTable)->where($localKey, data_get($record, 'id'))->pluck($relatedKey);

            $record->{$field['name'] . '__labels'} = DB::table($table)
                ->whereIn($field['value_column'] ?: 'id', $relatedIds)
                ->pluck($displayColumn)
                ->all();
        }
    }

    protected function hydratePivotSelections(Collection $fields, object $record): void
    {
        foreach ($fields as $field) {
            if ($field['component'] !== 'relationship' || ($field['relationship_type'] ?? 'belongsTo') !== 'belongsToMany' || blank($field['pivot_table'] ?? null)) {
                continue;
            }

            [$pivotTable, $localKey, $relatedKey] = $this->pivotKeys(null, $field);

            $record->{$field['name']} = DB::table($pivotTable)->where($localKey, data_get($record, 'id'))->pluck($relatedKey)->all();
        }
    }

    /**
     * @return array{0: string, 1: string, 2: string} [pivot table, local FK column, related FK column]
     */
    protected function pivotKeys(?Bread $bread, array $field): array
    {
        $pivotTable = (string) $field['pivot_table'];
        $localKey = filled($field['pivot_local_key'] ?? null)
            ? (string) $field['pivot_local_key']
            : Str::singular($bread?->table_name ?? 'record') . '_id';
        $relatedKey = filled($field['pivot_related_key'] ?? null)
            ? (string) $field['pivot_related_key']
            : Str::singular((string) $field['related_model']) . '_id';

        return [$pivotTable, $localKey, $relatedKey];
    }

    /**
     * Read-only related records for each hasMany field on the show page,
     * looked up by the related table's foreign key pointing back at
     * this record's primary key.
     *
     * @param Collection<int, array> $hasManyFields
     * @return array<string, array{field: array, records: \Illuminate\Support\Collection}>
     */
    protected function relatedListingsFor(Collection $hasManyFields, object $record): array
    {
        return $hasManyFields
            ->filter(fn (array $field): bool => filled($field['related_model'] ?? null) && filled($field['foreign_key'] ?? null))
            ->mapWithKeys(function (array $field) use ($record): array {
                $table = (string) $field['related_model'];

                if (! Schema::hasTable($table) || ! Schema::hasColumn($table, (string) $field['foreign_key'])) {
                    return [$field['name'] => ['field' => $field, 'records' => collect()]];
                }

                $records = DB::table($table)
                    ->where((string) $field['foreign_key'], data_get($record, 'id'))
                    ->limit(50)
                    ->get();

                return [$field['name'] => ['field' => $field, 'records' => $records]];
            })
            ->all();
    }

    protected function exportValue(array $field, object $record): string
    {
        $name = $field['name'];

        if ($field['component'] === 'relationship') {
            return (string) ($record->{$name . '__label'} ?? data_get($record, $name) ?? '');
        }

        $value = data_get($record, $name);
        $decoded = is_string($value) ? json_decode($value, true) : $value;

        if (in_array($field['component'], ['boolean', 'switch'], true)) {
            return $value ? 'Yes' : 'No';
        }

        if (in_array($field['component'], ['checkbox', 'tags'], true) && is_array($decoded)) {
            return implode(', ', $decoded);
        }

        if ($field['component'] === 'multiple_images' && is_array($decoded)) {
            return (string) count($decoded) . ' images';
        }

        return (string) ($value ?? '');
    }

    protected function logActivity(string $action, Bread $bread, string $record): void
    {
        ActivityLog::query()->create([
            'user_id' => auth('sjadmin')->id(),
            'action' => $action,
            'subject_type' => $bread->table_name,
            'subject_id' => is_numeric($record) ? (int) $record : null,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
            'meta' => ['bread' => $bread->slug, 'record' => $record],
        ]);
    }
}
