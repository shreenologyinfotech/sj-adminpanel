<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Safarjaisur\AdminPanel\Models\AdminUser;
use Safarjaisur\AdminPanel\Notifications\AdminAlertNotification;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class BackupController extends Controller
{
    public function index(): View
    {
        return view('sjadminpanel::backups.index', [
            'backups' => $this->backupFiles(),
        ]);
    }

    public function store(): RedirectResponse
    {
        try {
            $filename = $this->createDatabaseBackup();
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Backup failed: ' . $exception->getMessage());
        }

        $recipients = AdminUser::query()
            ->whereHas('roles.permissions', fn ($q) => $q->where('slug', 'backups.manage'))
            ->get();

        Notification::send($recipients, new AdminAlertNotification(
            "Database backup \"{$filename}\" completed successfully.",
            route('sjadmin.backups.index')
        ));

        return redirect()
            ->route('sjadmin.backups.index')
            ->with('success', "Database backup [{$filename}] created.");
    }

    public function download(string $file): StreamedResponse
    {
        $path = $this->backupPath($file);

        abort_unless($this->disk()->exists($path), 404);

        return $this->disk()->download($path, basename($file));
    }

    public function restore(string $file): RedirectResponse
    {
        $path = $this->backupPath($file);

        abort_unless($this->disk()->exists($path), 404);

        try {
            DB::unprepared($this->disk()->get($path));
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Restore failed: ' . $exception->getMessage());
        }

        return redirect()->route('sjadmin.backups.index')->with('success', "Database backup [{$file}] restored.");
    }

    public function destroy(string $file): RedirectResponse
    {
        $path = $this->backupPath($file);

        abort_unless($this->disk()->exists($path), 404);

        $this->disk()->delete($path);

        return redirect()->route('sjadmin.backups.index')->with('success', 'Backup deleted.');
    }

    protected function createDatabaseBackup(): string
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $database = $connection->getDatabaseName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new \RuntimeException("Database backups currently support MySQL and MariaDB, not [{$driver}].");
        }

        $filename = sprintf('%s-%s.sql', Str::slug(config('app.name', 'laravel')), now()->format('Ymd-His'));
        $contents = $this->buildMySqlDump($database);

        $this->disk()->put($this->backupPath($filename), $contents);

        return $filename;
    }

    protected function buildMySqlDump(string $database): string
    {
        $connection = DB::connection();
        $pdo = $connection->getPdo();
        $tables = $connection->select(
            "select table_name as name from information_schema.tables where table_schema = ? and table_type in ('BASE TABLE', 'SYSTEM VERSIONED') order by table_name",
            [$database]
        );

        $dump = [
            '-- Safarjaisur AdminPanel database backup',
            '-- Generated at: ' . now()->toDateTimeString(),
            '-- Database: ' . $database,
            '',
            'SET FOREIGN_KEY_CHECKS=0;',
            '',
        ];

        foreach ($tables as $table) {
            $tableName = (string) $table->name;
            $quotedTable = $this->quoteIdentifier($tableName);
            $create = (array) $connection->selectOne('SHOW CREATE TABLE ' . $quotedTable);
            $createSql = $create['Create Table'] ?? array_values($create)[1] ?? '';

            $dump[] = '-- --------------------------------------------------------';
            $dump[] = '-- Table structure for ' . $quotedTable;
            $dump[] = 'DROP TABLE IF EXISTS ' . $quotedTable . ';';
            $dump[] = $createSql . ';';
            $dump[] = '';

            foreach ($connection->table($tableName)->cursor() as $row) {
                $values = array_map(
                    fn (mixed $value): string => $value === null ? 'NULL' : $pdo->quote((string) $value),
                    (array) $row
                );

                $dump[] = 'INSERT INTO ' . $quotedTable . ' VALUES (' . implode(', ', $values) . ');';
            }

            $dump[] = '';
        }

        $dump[] = 'SET FOREIGN_KEY_CHECKS=1;';
        $dump[] = '';

        return implode(PHP_EOL, $dump);
    }

    protected function backupFiles(): Collection
    {
        $disk = $this->disk();
        $directory = $this->backupDirectory();

        if (! $disk->exists($directory)) {
            $disk->makeDirectory($directory);
        }

        return collect($disk->files($directory))
            ->filter(fn (string $path): bool => Str::endsWith($path, '.sql'))
            ->map(fn (string $path): array => [
                'name' => basename($path),
                'size' => $disk->size($path),
                'modified' => $disk->lastModified($path),
            ])
            ->sortByDesc('modified')
            ->values();
    }

    protected function backupPath(string $file): string
    {
        $file = basename($file);

        abort_if($file === '' || ! Str::endsWith($file, '.sql'), 404);

        return $this->backupDirectory() . '/' . $file;
    }

    protected function backupDirectory(): string
    {
        return trim((string) config('sjadminpanel.backups.path', 'sjadmin-backups'), '/');
    }

    protected function disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk((string) config('sjadminpanel.backups.disk', 'local'));
    }

    protected function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
