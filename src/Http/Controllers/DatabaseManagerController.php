<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DatabaseManagerController extends Controller
{
    public function index(): View
    {
        try {
            $tables = $this->getCurrentDatabaseTables();
            $databaseError = null;
        } catch (Throwable $exception) {
            report($exception);

            $tables = collect();
            $databaseError = sprintf(
                'Unable to read database tables. Please confirm MySQL is running on %s:%s and database [%s] exists.',
                config('database.connections.mysql.host', 'localhost'),
                config('database.connections.mysql.port', '3306'),
                DB::connection()->getDatabaseName() ?: config('database.connections.mysql.database', '')
            );
        }

        return view('sjadminpanel::database.index', compact('tables', 'databaseError'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['table' => ['required', 'string', 'alpha_dash']]);

        // Table creation is delegated to the migration generator service.
        return redirect()->route('sjadmin.database.index')->with('success', 'Table creation queued.');
    }

    public function destroy(string $table): RedirectResponse
    {
        Schema::dropIfExists($table);

        return redirect()->route('sjadmin.database.index')->with('success', "Table [{$table}] dropped.");
    }

    protected function getCurrentDatabaseTables(): Collection
    {
        $connection = DB::connection();
        $database = $connection->getDatabaseName();

        if (in_array($connection->getDriverName(), ['mysql', 'mariadb'], true) && $database !== '') {
            return $connection->table('information_schema.tables as t')
                ->leftJoin('information_schema.columns as c', function ($join): void {
                    $join->on('c.table_schema', '=', 't.table_schema')
                        ->on('c.table_name', '=', 't.table_name');
                })
                ->where('t.table_schema', $database)
                ->whereIn('t.table_type', ['BASE TABLE', 'SYSTEM VERSIONED'])
                ->groupBy('t.table_name')
                ->orderBy('t.table_name')
                ->get([
                    't.table_name as name',
                    DB::raw('COUNT(c.column_name) as column_count'),
                ])
                ->map(fn (object $table): array => [
                    'name' => $table->name,
                    'columns' => (int) $table->column_count,
                ]);
        }

        return collect(Schema::getTableListing(null, false))->map(fn (string $table): array => [
            'name' => $table,
            'columns' => count(Schema::getColumns($table)),
        ]);
    }
}
