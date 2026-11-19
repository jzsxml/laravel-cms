<?php

namespace App\Support;

class CmsTable
{
    public static function name(string $table): string
    {
        return config('cms.table_prefix', 'fa_').$table;
    }
}
