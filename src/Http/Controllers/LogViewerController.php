<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use SplFileObject;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LogViewerController extends Controller
{
    public function index(Request $request): View
    {
        $files = $this->logFiles();
        $selected = basename((string) $request->query('file', $files->first()['name'] ?? ''));

        if ($selected !== '' && ! $files->contains('name', $selected)) {
            $selected = $files->first()['name'] ?? '';
        }

        $lines = $selected !== '' ? $this->tail($this->logPath($selected)) : [];
        $lines = $this->filterLines($lines, (string) $request->query('search', ''), (string) $request->query('level', ''));

        return view('sjadminpanel::logs.index', [
            'files' => $files,
            'selected' => $selected,
            'lines' => $lines,
            'maxLines' => (int) config('sjadminpanel.logs.max_lines', 500),
            'levels' => ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'],
        ]);
    }

    public function download(string $file): BinaryFileResponse
    {
        return response()->download($this->logPath($file), basename($file));
    }

    public function destroy(string $file): RedirectResponse
    {
        File::delete($this->logPath($file));

        return redirect()->route('sjadmin.logs.index')->with('success', 'Log file deleted.');
    }

    protected function logFiles(): Collection
    {
        $path = storage_path('logs');

        if (! File::isDirectory($path)) {
            return collect();
        }

        return collect(File::files($path))
            ->filter(fn (\SplFileInfo $file): bool => Str::endsWith($file->getFilename(), '.log'))
            ->map(fn (\SplFileInfo $file): array => [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'modified' => $file->getMTime(),
            ])
            ->sortByDesc('modified')
            ->values();
    }

    protected function logPath(string $file): string
    {
        $file = basename($file);
        $path = storage_path('logs/' . $file);

        abort_if($file === '' || ! Str::endsWith($file, '.log') || ! File::isFile($path), 404);

        return $path;
    }

    protected function tail(string $path): array
    {
        $lines = max(1, (int) config('sjadminpanel.logs.max_lines', 500));
        $file = new SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);

        $start = max(0, $file->key() - $lines);
        $output = [];

        $file->seek($start);

        while (! $file->eof()) {
            $output[] = rtrim((string) $file->fgets(), "\r\n");
        }

        return array_filter($output, fn (string $line): bool => $line !== '');
    }

    protected function filterLines(array $lines, string $search, string $level): array
    {
        $search = Str::lower(trim($search));
        $level = Str::lower(trim($level));

        return array_values(array_filter($lines, function (string $line) use ($search, $level): bool {
            if ($level !== '' && ! str_contains(Str::lower($line), ".{$level}:")) {
                return false;
            }

            return $search === '' || str_contains(Str::lower($line), $search);
        }));
    }
}
