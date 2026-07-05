<?php

declare(strict_types=1);

namespace safarjaisur\AdminPanel\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use safarjaisur\AdminPanel\Models\Setting;

class BreadService
{
    public function getTables(): array
    {
        return DB::connection()->getDoctrineSchemaManager()->listTableNames();
    }

    public function getTableColumns(string $tableName): array
    {
        return Schema::getColumnListing($tableName);
    }

    public function getBreadConfiguration(string $tableName): ?array
    {
        // Read configuration from metadata tables
        return DB::table('sj_bread_configs')->where('table_name', $tableName)->first() 
            ? (array) DB::table('sj_bread_configs')->where('table_name', $tableName)->first() 
            : null;
    }

    public function saveBread(string $tableName, array $config, array $fields): void
    {
        DB::transaction(function () use ($tableName, $config, $fields) {
            DB::table('sj_bread_configs')->updateOrInsert(
                ['table_name' => $tableName],
                $config
            );

            // Rebuild fields mapping
            $configRecord = DB::table('sj_bread_configs')->where('table_name', $tableName)->first();
            DB::table('sj_bread_fields')->where('bread_id', $configRecord->id)->delete();

            foreach ($fields as $field) {
                $field['bread_id'] = $configRecord->id;
                DB::table('sj_bread_fields')->insert($field);
            }
        });
    }
}