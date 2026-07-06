<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Safarjaisur\AdminPanel\Models\Permission;

class PermissionController extends Controller
{
    public function index(): View
    {
        return view('sjadminpanel::permissions.index', [
            'permissions' => Permission::query()->orderBy('group')->paginate(30),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'unique:sjadmin_permissions,slug'],
            'group' => ['required', 'string', 'max:255'],
        ]);

        Permission::query()->create($data);

        return redirect()->route('sjadmin.permissions.index')->with('success', 'Permission created.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()->route('sjadmin.permissions.index')->with('success', 'Permission deleted.');
    }
}
