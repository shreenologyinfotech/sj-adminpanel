<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Safarjaisur\AdminPanel\Models\Menu;
use Safarjaisur\AdminPanel\Models\MenuItem;
use Safarjaisur\AdminPanel\Models\Permission;

class MenuBuilderController extends Controller
{
    public function index(): View
    {
        $menu = Menu::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin Sidebar']);
        $items = $menu->items()->with('children')->get();
        $allItems = $menu->allItems()->get();

        return view('sjadminpanel::menu.index', [
            'menu' => $menu,
            'items' => $this->flattenItems($items),
            'allItems' => $allItems,
            'permissions' => Permission::query()->orderBy('group')->orderBy('name')->get(),
            'routes' => collect(Route::getRoutes())
                ->map(fn ($route) => $route->getName())
                ->filter()
                ->sort()
                ->values(),
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
            'permission' => ['nullable', 'string'],
            'target' => ['required', 'in:_self,_blank'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data = $this->normalizeMenuData($data);

        MenuItem::query()->create($data);
        $this->clearMenuCache((int) $data['menu_id']);

        return redirect()->route('sjadmin.menu.index')->with('success', 'Menu item added.');
    }

    public function update(Request $request, MenuItem $menu): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string'],
            'url' => ['nullable', 'string'],
            'route' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:sjadmin_menu_items,id'],
            'permission' => ['nullable', 'string'],
            'target' => ['required', 'in:_self,_blank'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data = $this->normalizeMenuData($data);

        if ((int) ($data['parent_id'] ?? 0) === $menu->id) {
            return back()->with('error', 'A menu item cannot be its own parent.');
        }

        if ($data['parent_id'] && $this->isDescendant((int) $data['parent_id'], $menu->id)) {
            return back()->with('error', 'A menu item cannot be moved below one of its own children.');
        }

        $menu->update($data);
        $this->clearMenuCache((int) $menu->menu_id);

        return redirect()->route('sjadmin.menu.index')->with('success', 'Menu item updated.');
    }

    public function destroy(MenuItem $menu): RedirectResponse
    {
        $menuId = (int) $menu->menu_id;

        $menu->delete();
        $this->clearMenuCache($menuId);

        return redirect()->route('sjadmin.menu.index')->with('success', 'Menu item removed.');
    }

    protected function flattenItems(Collection $items, int $depth = 0): Collection
    {
        return $items->flatMap(function (MenuItem $item) use ($depth) {
            $item->setAttribute('depth', $depth);

            return collect([$item])->merge($this->flattenItems($item->children, $depth + 1));
        });
    }

    protected function normalizeMenuData(array $data): array
    {
        foreach (['icon', 'url', 'route', 'permission', 'parent_id'] as $key) {
            if (array_key_exists($key, $data) && blank($data[$key])) {
                $data[$key] = null;
            }
        }

        $data['order'] = (int) ($data['order'] ?? 0);

        return $data;
    }

    protected function isDescendant(int $candidateParentId, int $itemId): bool
    {
        $candidate = MenuItem::query()->find($candidateParentId);

        while ($candidate) {
            if ((int) $candidate->parent_id === $itemId) {
                return true;
            }

            $candidate = $candidate->parent;
        }

        return false;
    }

    protected function clearMenuCache(int $menuId): void
    {
        $slug = Menu::query()->whereKey($menuId)->value('slug');

        if ($slug) {
            Cache::forget("sjadmin.menu.{$slug}");
        }

        Cache::forget('sjadmin.menu.admin');
    }
}
