<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileManagerController extends Controller
{
    public function index(Request $request): View
    {
        $path = $this->path($request->query('path', ''));
        $disk = $this->disk();

        if (! $disk->exists($path)) {
            $disk->makeDirectory($path);
        }

        $files = collect($disk->files($path))
            ->map(fn (string $file): array => [
                'path' => $file,
                'name' => basename($file),
                'size' => $disk->size($file),
                'modified' => $disk->lastModified($file),
            ]);

        $folders = collect($disk->directories($path))
            ->map(fn (string $folder): array => [
                'path' => $folder,
                'name' => basename($folder),
            ]);

        if ($request->filled('search')) {
            $search = Str::lower((string) $request->query('search'));
            $files = $files->filter(fn (array $file): bool => str_contains(Str::lower($file['name']), $search));
            $folders = $folders->filter(fn (array $folder): bool => str_contains(Str::lower($folder['name']), $search));
        }

        return view('sjadminpanel::files.index', [
            'path' => $path,
            'parentPath' => $this->parentPath($path),
            'folders' => $folders->values(),
            'files' => $files->values(),
        ]);
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'path' => ['nullable', 'string'],
            'files' => ['required', 'array'],
            'files.*' => ['file', 'max:' . config('sjadminpanel.files.max_upload_size', 20480)],
        ]);

        $path = $this->path($request->input('path', ''));

        foreach ($request->file('files') as $file) {
            $this->disk()->putFileAs($path, $file, $file->getClientOriginalName());
        }

        return redirect()->route('sjadmin.files.index', ['path' => $path])->with('success', 'Files uploaded.');
    }

    public function folder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'path' => ['nullable', 'string'],
            'name' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9 _.-]+$/'],
        ]);

        $path = trim($this->path($data['path'] ?? '') . '/' . $data['name'], '/');
        $this->disk()->makeDirectory($path);

        return redirect()->route('sjadmin.files.index', ['path' => $this->path($data['path'] ?? '')])->with('success', 'Folder created.');
    }

    public function rename(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'path' => ['required', 'string'],
            'name' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9 _.-]+$/'],
        ]);

        $path = $this->path($data['path']);
        $target = trim(dirname($path) . '/' . $data['name'], './');

        abort_unless($this->disk()->exists($path), 404);
        abort_if($this->disk()->exists($target), 422, 'A file or folder with that name already exists.');

        $this->movePath($path, $target);

        return back()->with('success', 'Renamed.');
    }

    public function download(Request $request): StreamedResponse
    {
        $path = $this->path($request->query('path', ''));

        abort_unless($this->disk()->exists($path), 404);

        return $this->disk()->download($path, basename($path));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $path = $this->path($request->input('path', ''));

        abort_unless($this->disk()->exists($path), 404);

        if ($this->disk()->directoryExists($path)) {
            $this->disk()->deleteDirectory($path);
        } else {
            $this->disk()->delete($path);
        }

        return back()->with('success', 'Deleted.');
    }

    protected function movePath(string $path, string $target): void
    {
        if (! $this->disk()->directoryExists($path)) {
            $this->disk()->move($path, $target);

            return;
        }

        $this->disk()->makeDirectory($target);

        foreach ($this->disk()->allFiles($path) as $file) {
            $this->disk()->move($file, $target . '/' . Str::after($file, trim($path, '/') . '/'));
        }

        $this->disk()->deleteDirectory($path);
    }

    protected function path(mixed $path): string
    {
        $root = trim((string) config('sjadminpanel.files.root', 'file-manager'), '/');
        $path = trim(str_replace('\\', '/', (string) $path), '/');
        $path = collect(explode('/', $path))
            ->reject(fn (string $segment): bool => $segment === '' || $segment === '.' || $segment === '..')
            ->implode('/');

        if ($root !== '' && ! Str::startsWith($path, $root)) {
            $path = trim($root . '/' . $path, '/');
        }

        return $path;
    }

    protected function parentPath(string $path): string
    {
        $root = trim((string) config('sjadminpanel.files.root', 'file-manager'), '/');

        if ($path === $root || $path === '') {
            return '';
        }

        return dirname($path) === '.' ? '' : dirname($path);
    }

    protected function disk(): FilesystemAdapter
    {
        return Storage::disk((string) config('sjadminpanel.files.disk', 'local'));
    }
}
