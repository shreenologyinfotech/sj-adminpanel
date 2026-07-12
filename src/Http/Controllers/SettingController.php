<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Safarjaisur\AdminPanel\Contracts\Repositories\SettingRepositoryInterface;
use Safarjaisur\AdminPanel\Models\Setting;

class SettingController extends Controller
{
    public function index(): View
    {
        $this->ensureDefaultSettings();

        return view('sjadminpanel::settings.index', [
            'groups' => Setting::query()
                ->orderBy('group')
                ->orderBy('key')
                ->get()
                ->groupBy('group'),
        ]);
    }

    public function update(Request $request, SettingRepositoryInterface $settings): RedirectResponse
    {
        foreach (Setting::query()->where('type', 'json')->get() as $setting) {
            $value = $request->input("settings.{$setting->key}");

            if (! blank($value) && json_decode((string) $value) === null && json_last_error() !== JSON_ERROR_NONE) {
                return back()
                    ->withErrors(["settings.{$setting->key}" => 'The value must be valid JSON.'])
                    ->withInput();
            }
        }

        foreach (Setting::query()->get() as $setting) {
            $settings->set(
                $setting->key,
                $this->valueFromRequest($request, $setting),
                $setting->group,
                $setting->type
            );
        }

        return redirect()->route('sjadmin.settings.index')->with('success', 'Settings saved.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'group' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_.-]+$/', 'unique:sjadmin_settings,key'],
            'type' => ['required', 'string', 'in:text,textarea,boolean,number,image,file,select,json'],
            'value' => ['nullable'],
        ]);

        $data['group'] = str($data['group'])->lower()->slug('_')->value();

        Setting::query()->create($data);
        Cache::forget('sjadmin.settings');

        return redirect()->route('sjadmin.settings.index')->with('success', 'Setting created.');
    }

    public function destroy(Setting $setting, SettingRepositoryInterface $settings): RedirectResponse
    {
        $setting->delete();
        Cache::forget('sjadmin.settings');

        return redirect()->route('sjadmin.settings.index')->with('success', 'Setting deleted.');
    }

    protected function valueFromRequest(Request $request, Setting $setting): mixed
    {
        if (in_array($setting->type, ['image', 'file'], true)) {
            $files = $request->file('setting_files', []);

            if (! isset($files[$setting->key])) {
                return $setting->value;
            }

            return $files[$setting->key]->store('settings', config('sjadminpanel.storage.disk'));
        }

        $values = $request->input('settings', []);

        if ($setting->type === 'boolean') {
            return (bool) ($values[$setting->key] ?? false) ? '1' : '0';
        }

        if ($setting->type === 'json') {
            $value = $values[$setting->key] ?? null;

            return blank($value) ? null : $value;
        }

        if ($setting->type === 'number') {
            return $values[$setting->key] ?? null;
        }

        return $values[$setting->key] ?? null;
    }

    protected function ensureDefaultSettings(): void
    {
        foreach ($this->defaultSettings() as $setting) {
            Setting::query()->firstOrCreate(['key' => $setting['key']], $setting);
        }
    }

    protected function defaultSettings(): array
    {
        return [
            ['group' => 'general', 'key' => 'site.name', 'value' => 'Admin Panel', 'type' => 'text'],
            ['group' => 'general', 'key' => 'site.tagline', 'value' => null, 'type' => 'text'],
            ['group' => 'general', 'key' => 'site.logo', 'value' => null, 'type' => 'image'],
            ['group' => 'general', 'key' => 'site.favicon', 'value' => null, 'type' => 'image'],
            ['group' => 'general', 'key' => 'site.maintenance_mode', 'value' => '0', 'type' => 'boolean'],
            ['group' => 'appearance', 'key' => 'theme.dark_mode', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'appearance', 'key' => 'theme.rtl', 'value' => '0', 'type' => 'boolean'],
            ['group' => 'mail', 'key' => 'mail.from_address', 'value' => null, 'type' => 'text'],
            ['group' => 'mail', 'key' => 'mail.from_name', 'value' => 'Admin Panel', 'type' => 'text'],
            ['group' => 'seo', 'key' => 'seo.title', 'value' => null, 'type' => 'text'],
            ['group' => 'seo', 'key' => 'seo.description', 'value' => null, 'type' => 'textarea'],
            ['group' => 'seo', 'key' => 'seo.keywords', 'value' => null, 'type' => 'textarea'],
            ['group' => 'social', 'key' => 'social.facebook', 'value' => null, 'type' => 'text'],
            ['group' => 'social', 'key' => 'social.instagram', 'value' => null, 'type' => 'text'],
            ['group' => 'social', 'key' => 'social.linkedin', 'value' => null, 'type' => 'text'],
            ['group' => 'analytics', 'key' => 'analytics.google_id', 'value' => null, 'type' => 'text'],
            ['group' => 'analytics', 'key' => 'analytics.scripts', 'value' => null, 'type' => 'textarea'],
            ['group' => 'advanced', 'key' => 'advanced.custom_json', 'value' => null, 'type' => 'json'],
        ];
    }
}
