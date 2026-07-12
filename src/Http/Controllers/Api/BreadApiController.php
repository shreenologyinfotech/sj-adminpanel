<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Safarjaisur\AdminPanel\Models\Bread;

/**
 * A thin JSON wrapper around the same table-driven CRUD the web resource
 * controller performs, so every BREAD data type automatically gets a
 * REST endpoint without any extra configuration. Kept intentionally
 * simple (no relationship expansion, no bulk ops) — anything beyond
 * basic CRUD over the API is a candidate for a future, purpose-built
 * API resource class per data type.
 */
class BreadApiController extends Controller
{
    public function index(Request $request, Bread $bread): JsonResponse
    {
        $records = DB::table($bread->table_name)
            ->orderByDesc($this->primaryKey($bread))
            ->paginate((int) $request->integer('per_page', config('sjadminpanel.pagination.per_page')));

        return response()->json($records);
    }

    public function show(Bread $bread, string $record): JsonResponse
    {
        $row = DB::table($bread->table_name)->where($this->primaryKey($bread), $record)->first();

        abort_unless($row, 404);

        return response()->json($row);
    }

    public function store(Request $request, Bread $bread): JsonResponse
    {
        $columns = Schema::getColumnListing($bread->table_name);
        $data = $request->except(['api_token']);

        if (in_array('created_at', $columns, true)) {
            $data['created_at'] = $data['updated_at'] = now();
        }

        $id = DB::table($bread->table_name)->insertGetId($data);

        return response()->json(DB::table($bread->table_name)->where($this->primaryKey($bread), $id)->first(), 201);
    }

    public function update(Request $request, Bread $bread, string $record): JsonResponse
    {
        $columns = Schema::getColumnListing($bread->table_name);
        $data = $request->except(['api_token']);

        if (in_array('updated_at', $columns, true)) {
            $data['updated_at'] = now();
        }

        DB::table($bread->table_name)->where($this->primaryKey($bread), $record)->update($data);

        return response()->json(DB::table($bread->table_name)->where($this->primaryKey($bread), $record)->first());
    }

    public function destroy(Bread $bread, string $record): JsonResponse
    {
        DB::table($bread->table_name)->where($this->primaryKey($bread), $record)->delete();

        return response()->json(['deleted' => true]);
    }

    protected function primaryKey(Bread $bread): string
    {
        $columns = Schema::getColumnListing($bread->table_name);

        return in_array('id', $columns, true) ? 'id' : ($columns[0] ?? 'id');
    }
}
