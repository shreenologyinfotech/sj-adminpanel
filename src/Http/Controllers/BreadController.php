<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Safarjaisur\AdminPanel\Models\Bread;
use Safarjaisur\AdminPanel\Models\Permission;
use Safarjaisur\AdminPanel\Models\Role;

class BreadController extends Controller
{
    /**
     * Every data type gets one permission per ability. These are what
     * EnsureBreadPermission checks against the "{slug}.{ability}" name.
     */
    protected const ABILITIES = ['browse', 'read', 'edit', 'add', 'delete'];

    public function index(): View
    {
        return view('sjadminpanel::bread.index', ['breads' => Bread::query()->latest()->paginate(20)]);
    }

    public function create(): View
    {
        $connection = DB::connection();
        $schema = in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)
            ? $connection->getDatabaseName()
            : null;

        return view('sjadminpanel::bread.create', ['tables' => Schema::getTableListing($schema, false)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'unique:sjadmin_breads,slug'],
            'table_name' => ['required', 'string'],
            'icon' => ['nullable', 'string'],
        ]);

        $data['fields'] = $this->buildFieldsFromTable($data['table_name']);

        $bread = Bread::query()->create($data);

        $this->syncAbilityPermissions($bread);

        return redirect()->route('sjadmin.bread.index')->with('success', 'BREAD created.');
    }

    public function edit(Bread $bread): View
    {
        return view('sjadminpanel::bread.edit', compact('bread'));
    }

    public function update(Request $request, Bread $bread): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string'],
            'fields' => ['array'],
        ]);

        $data['fields'] = $this->normalizeSubmittedFields($data['fields'] ?? []);

        $bread->update($data);

        $this->syncAbilityPermissions($bread);

        return redirect()->route('sjadmin.bread.index')->with('success', 'BREAD updated.');
    }

    public function destroy(Bread $bread): RedirectResponse
    {
        $slug = $bread->slug;

        $bread->delete();

        Permission::query()
            ->whereIn('slug', $this->abilityPermissionSlugs($slug))
            ->delete();

        return redirect()->route('sjadmin.bread.index')->with('success', 'BREAD deleted.');
    }

    /**
     * Create/refresh the five "{slug}.{ability}" permissions for this data
     * type and grant them to the Administrator role so upgrading this
     * package never locks existing full-access admins out of their data.
     */
    protected function syncAbilityPermissions(Bread $bread): void
    {
        $administrator = Role::query()->where('slug', 'administrator')->first();
        $permissionIds = [];

        foreach (self::ABILITIES as $ability) {
            $permission = Permission::query()->firstOrCreate(
                ['slug' => "{$bread->slug}.{$ability}"],
                ['name' => Str::headline($ability), 'group' => $bread->name]
            );

            // Keep the display name/group in sync if the bread was renamed.
            if ($permission->group !== $bread->name) {
                $permission->update(['group' => $bread->name]);
            }

            $permissionIds[] = $permission->id;
        }

        $administrator?->permissions()->syncWithoutDetaching($permissionIds);
    }

    /**
     * @return array<int, string>
     */
    protected function abilityPermissionSlugs(string $slug): array
    {
        return array_map(static fn (string $ability): string => "{$slug}.{$ability}", self::ABILITIES);
    }

    protected function buildFieldsFromTable(string $table): array
    {
        return collect(Schema::getColumns($table))->values()->map(function (array $column, int $index): array {
            $name = (string) $column['name'];
            $type = (string) ($column['type'] ?? $column['type_name'] ?? 'string');
            $typeName = (string) ($column['type_name'] ?? $type);
            $locked = in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'], true);
            $sensitive = in_array($name, ['password', 'remember_token'], true);
            $hasDefault = array_key_exists('default', $column) && $column['default'] !== null;

            return [
                'name' => $name,
                'label' => Str::headline($name),
                'type' => $type,
                'type_name' => $typeName,
                'component' => $this->componentForType($type, $name),
                'order' => $index + 1,
                'nullable' => (bool) ($column['nullable'] ?? true),
                'required' => ! (bool) ($column['nullable'] ?? true) && ! $locked && ! $hasDefault,
                'unique' => false,
                'default' => $column['default'] ?? null,
                'validation' => null,
                'options' => null,
                'placeholder' => null,
                'help_text' => null,
                'width' => 'col-md-12',
                'tab' => null,
                'panel' => null,
                'hidden' => false,
                'slug' => false,
                'slug_from' => null,
                'translatable' => false,
                'relationship' => false,
                'relationship_type' => null,
                'related_model' => null,
                'value_column' => 'id',
                'display_column' => 'name',
                'browse' => ! $sensitive,
                'read' => ! $sensitive,
                'edit' => ! $locked && $name !== 'remember_token',
                'add' => ! $locked && $name !== 'remember_token',
            ];
        })->values()->all();
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

    protected function normalizeSubmittedFields(array $fields): array
    {
        $components = $this->allowedComponents();

        return collect($fields)->map(function (array $field, int $index) use ($components): array {
            $name = (string) ($field['name'] ?? '');
            $type = (string) ($field['type'] ?? $field['type_name'] ?? 'string');
            $component = (string) ($field['component'] ?? $this->componentForType($type, $name));

            if (! in_array($component, $components, true)) {
                $component = 'text';
            }

            $field['name'] = $name;
            $field['label'] = filled($field['label'] ?? null) ? (string) $field['label'] : Str::headline($name);
            $field['type'] = $type;
            $field['type_name'] = (string) ($field['type_name'] ?? $type);
            $field['component'] = $component;
            $field['order'] = max(0, (int) ($field['order'] ?? ($index + 1)));

            foreach ([
                'validation',
                'options',
                'default',
                'placeholder',
                'help_text',
                'width',
                'tab',
                'panel',
                'slug_from',
                'relationship_type',
                'related_model',
                'value_column',
                'display_column',
                'pivot_table',
                'pivot_local_key',
                'pivot_related_key',
                'foreign_key',
            ] as $key) {
                $field[$key] = blank($field[$key] ?? null) ? null : (string) $field[$key];
            }

            $field['width'] ??= 'col-md-12';
            $field['value_column'] ??= 'id';
            $field['display_column'] ??= 'name';
            $field['relationship_type'] ??= 'belongsTo';

            foreach (['required', 'nullable', 'unique', 'browse', 'read', 'edit', 'add', 'hidden', 'slug', 'translatable', 'relationship'] as $flag) {
                $field[$flag] = (bool) ($field[$flag] ?? false);
            }

            return $field;
        })->sortBy('order')->values()->all();
    }

    protected function allowedComponents(): array
    {
        return [
            'text',
            'textarea',
            'editor',
            'markdown',
            'number',
            'boolean',
            'switch',
            'select',
            'radio',
            'checkbox',
            'date',
            'datetime',
            'time',
            'file',
            'image',
            'multiple_images',
            'json',
            'code',
            'color',
            'tags',
            'slug',
            'email',
            'password',
            'hidden',
            'relationship',
        ];
    }
}
