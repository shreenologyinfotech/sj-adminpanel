<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Safarjaisur\AdminPanel\Models\Bread;

class BreadController extends Controller
{
    public function index(): View
    {
        return view('sjadminpanel::bread.index', ['breads' => Bread::query()->latest()->paginate(20)]);
    }

    public function create(): View
    {
        return view('sjadminpanel::bread.create', ['tables' => Schema::getTableListing()]);
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

        Bread::query()->create($data);

        return redirect()->route('sjadmin.bread.index')->with('success', 'BREAD created.');
    }

    public function edit(Bread $bread): View
    {
        return view('sjadminpanel::bread.edit', compact('bread'));
    }

    public function update(Request $request, Bread $bread): RedirectResponse
    {
        $bread->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string'],
            'fields' => ['array'],
        ]));

        return redirect()->route('sjadmin.bread.index')->with('success', 'BREAD updated.');
    }

    public function destroy(Bread $bread): RedirectResponse
    {
        $bread->delete();

        return redirect()->route('sjadmin.bread.index')->with('success', 'BREAD deleted.');
    }

    protected function buildFieldsFromTable(string $table): array
    {
        return collect(Schema::getColumns($table))->map(fn (array $column) => [
            'name' => $column['name'],
            'type' => $column['type_name'],
            'nullable' => $column['nullable'],
            'browse' => true,
            'read' => true,
            'edit' => ! in_array($column['name'], ['id', 'created_at', 'updated_at'], true),
            'add' => ! in_array($column['name'], ['id', 'created_at', 'updated_at'], true),
        ])->values()->all();
    }
}
