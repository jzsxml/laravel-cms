<?php

namespace App\Crud;

use Illuminate\Support\Facades\Schema;

class ColumnIntrospector
{
    /**
     * @return array<int, array{name: string, type_name: string, comment: string, nullable: bool}>
     */
    public function columns(string $table): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        $raw = Schema::getColumns($table);
        $columns = [];
        foreach ($raw as $column) {
            $columns[] = [
                'name' => $column['name'],
                'type_name' => strtolower($column['type_name'] ?? 'string'),
                'comment' => (string) ($column['comment'] ?? $column['name']),
                'nullable' => (bool) ($column['nullable'] ?? true),
            ];
        }

        return $columns;
    }

    public function hasColumn(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }
}
