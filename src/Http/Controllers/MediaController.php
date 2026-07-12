<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Safarjaisur\AdminPanel\Services\MediaThumbnailService;

class MediaController extends Controller
{
    public function __construct(protected MediaThumbnailService $thumbnails)
    {
    }

    public function index(Request $request): View
    {
        $disk = Storage::disk(config('sjadminpanel.storage.disk'));
        $path = config('sjadminpanel.storage.media_path') . '/' . trim((string) $request->query('folder', ''), '/');

        $files = collect($disk->files($path))
            ->reject(fn (string $file): bool => $this->isThumbnailVariant($file) || basename($file) === '.keep')
            ->values();

        return view('sjadminpanel::media.index', [
            'folders' => $disk->directories($path),
            'files' => $files,
            'currentPath' => $path,
            'thumbnails' => $this->thumbnails,
            'disk' => $disk,
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
        $directory = config('sjadminpanel.storage.media_path') . '/' . $folder;

        foreach ($request->file('files') as $file) {
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $disk->putFileAs($directory, $file, $filename);

            if ($path) {
                $this->thumbnails->generate($disk, $path);
            }
        }

        return redirect()->route('sjadmin.media.index', ['folder' => $folder])->with('success', 'Files uploaded.');
    }

    /**
     * Creates a new (empty) sub-folder under the current media path.
     * Flysystem has no real directories, so we drop a hidden placeholder
     * file to make the folder show up before anything is uploaded to it.
     */
    public function folder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'folder' => ['nullable', 'string'],
            'name' => ['required', 'string', 'max:255', 'regex:/^[\w\-\s]+$/'],
        ]);

        $disk = Storage::disk(config('sjadminpanel.storage.disk'));
        $parent = trim((string) ($data['folder'] ?? ''), '/');
        $path = config('sjadminpanel.storage.media_path') . '/' . trim($parent . '/' . $data['name'], '/');

        $disk->put($path . '/.keep', '');

        return redirect()->route('sjadmin.media.index', ['folder' => $parent])->with('success', 'Folder created.');
    }

    public function rename(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'path' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $disk = Storage::disk(config('sjadminpanel.storage.disk'));
        $oldPath = $data['path'];
        $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
        $directory = pathinfo($oldPath, PATHINFO_DIRNAME);
        $newName = pathinfo($data['name'], PATHINFO_FILENAME) . ($extension ? ".{$extension}" : '');
        $newPath = ($directory === '.' ? '' : $directory . '/') . $newName;

        if ($disk->exists($oldPath) && ! $disk->exists($newPath)) {
            $this->thumbnails->delete($disk, $oldPath);
            $disk->move($oldPath, $newPath);
            $this->thumbnails->generate($disk, $newPath);
        }

        return back()->with('success', 'Renamed.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(['path' => ['required', 'string']]);

        $disk = Storage::disk(config('sjadminpanel.storage.disk'));
        $path = $request->string('path')->value();

        $this->thumbnails->delete($disk, $path);
        $disk->delete($path);

        return back()->with('success', 'File deleted.');
    }

    protected function isThumbnailVariant(string $path): bool
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);

        foreach (array_keys(config('sjadminpanel.media.thumbnails', [])) as $label) {
            if (Str::endsWith($filename, "-{$label}")) {
                return true;
            }
        }

        return false;
    }
}
