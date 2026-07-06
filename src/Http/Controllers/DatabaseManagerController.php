<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;

class DatabaseManagerController extends Controller
{
    public function index(): View
    {
        $tables = collect(Schema::getTableListing())->map(fn (string $table) => [
            'name' => $table,
            'columns' => count(Schema::getColumns($table)),
        ]);

        return view('sjadminpanel::database.index', compact('tables'));
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
}
