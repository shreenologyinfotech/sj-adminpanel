<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Safarjaisur\AdminPanel\Models\AdminUser;
use Safarjaisur\AdminPanel\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = AdminUser::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%'))
            ->with('roles')
            ->latest()
            ->paginate(config('sjadminpanel.pagination.per_page'));

        return view('sjadminpanel::users.index', compact('users'));
    }

    public function create(): View
    {
        return view('sjadminpanel::users.create', ['roles' => Role::all()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:sjadmin_users,email'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['array'],
        ]);

        $user = AdminUser::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'active',
        ]);

        $user->roles()->sync($data['roles'] ?? []);

        return redirect()->route('sjadmin.users.index')->with('success', 'User created.');
    }

    public function edit(AdminUser $user): View
    {
        return view('sjadminpanel::users.edit', ['user' => $user, 'roles' => Role::all()]);
    }

    public function update(Request $request, AdminUser $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:sjadmin_users,email,' . $user->id],
            'status' => ['required', 'in:active,inactive,banned'],
            'roles' => ['array'],
        ]);

        $user->update($data);
        $user->roles()->sync($data['roles'] ?? []);

        return redirect()->route('sjadmin.users.index')->with('success', 'User updated.');
    }

    public function destroy(AdminUser $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('sjadmin.users.index')->with('success', 'User deleted.');
    }
}
