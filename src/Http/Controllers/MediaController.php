<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $disk = Storage::disk(config('sjadminpanel.storage.disk'));
        $path = config('sjadminpanel.storage.media_path') . '/' . trim((string) $request->query('folder', ''), '/');

        return view('sjadminpanel::media.index', [
            'folders' => $disk->directories($path),
            'files' => $disk->files($path),
            'currentPath' => $path,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['file', 'max:' . config('sjadminpanel.media.max_upload_size')],
        ]);

        $disk = Storage::disk(config('sjadminpanel.storage.disk'));
        $folder = trim((string) $request->input('folder', ''), '/');

        foreach ($request->file('files') as $file) {
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $disk->putFileAs(config('sjadminpanel.storage.media_path') . '/' . $folder, $file, $filename);
        }

        return redirect()->route('sjadmin.media.index', ['folder' => $folder])->with('success', 'Files uploaded.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(['path' => ['required', 'string']]);

        Storage::disk(config('sjadminpanel.storage.disk'))->delete($request->string('path'));

        return back()->with('success', 'File deleted.');
    }
}
