<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Safarjaisur\AdminPanel\Models\Menu;
use Safarjaisur\AdminPanel\Models\MenuItem;

class MenuBuilderController extends Controller
{
    public function index(): View
    {
        $menu = Menu::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin Sidebar']);

        return view('sjadminpanel::menu.index', [
            'menu' => $menu,
            'items' => $menu->items()->with('children')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'menu_id' => ['required', 'exists:sjadmin_menus,id'],
            'title' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string'],
            'url' => ['nullable', 'string'],
            'route' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:sjadmin_menu_items,id'],
        ]);

        MenuItem::query()->create($data);

        return redirect()->route('sjadmin.menu.index')->with('success', 'Menu item added.');
    }

    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $menuItem->update($request->validate([
            'title' => ['required', 'string', 'max:255'],
            'order' => ['integer'],
        ]));

        return redirect()->route('sjadmin.menu.index')->with('success', 'Menu item updated.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->delete();

        return redirect()->route('sjadmin.menu.index')->with('success', 'Menu item removed.');
    }
}
