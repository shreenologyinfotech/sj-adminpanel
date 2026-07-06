<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Safarjaisur\AdminPanel\Contracts\Repositories\SettingRepositoryInterface;
use Safarjaisur\AdminPanel\Models\Setting;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('sjadminpanel::settings.index', [
            'groups' => Setting::query()->get()->groupBy('group'),
        ]);
    }

    public function update(Request $request, SettingRepositoryInterface $settings): RedirectResponse
    {
        foreach ($request->input('settings', []) as $key => $value) {
            $settings->set($key, $value);
        }

        return redirect()->route('sjadmin.settings.index')->with('success', 'Settings saved.');
    }
}
