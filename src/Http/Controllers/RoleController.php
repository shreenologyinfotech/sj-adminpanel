<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Safarjaisur\AdminPanel\Models\Bread;
use Safarjaisur\AdminPanel\Models\Permission;
use Safarjaisur\AdminPanel\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('sjadminpanel::roles.index', ['roles' => Role::with('permissions')->paginate(20)]);
    }

    public function create(): View
    {
        return view('sjadminpanel::roles.create', [
            'permissions' => Permission::all()->groupBy('group'),
            'breadNames' => Bread::query()->pluck('name')->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'unique:sjadmin_roles,slug'],
            'permissions' => ['array'],
        ]);

        $role = Role::query()->create($data);
        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()->route('sjadmin.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role): View
    {
        return view('sjadminpanel::roles.edit', [
            'role' => $role,
            'permissions' => Permission::all()->groupBy('group'),
            'breadNames' => Bread::query()->pluck('name')->all(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'permissions' => ['array'],
        ]);

        $role->update(['name' => $data['name']]);
        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()->route('sjadmin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();

        return redirect()->route('sjadmin.roles.index')->with('success', 'Role deleted.');
    }
}
