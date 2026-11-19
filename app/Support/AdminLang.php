<?php

namespace App\Support;

class AdminLang
{
    /** 列表/筛选字段 → 翻译键 */
    public static function field(string $name): string
    {
        $key = 'admin.fields.'.$name;
        $translated = __($key);

        return $translated !== $key ? $translated : self::humanize($name);
    }

    public static function ui(string $key, array $replace = []): string
    {
        return __('admin.ui.'.$key, $replace);
    }

    /**
     * @param  array<int, string>  $fields
     * @return array<string, string>
     */
    public static function labelsFor(array $fields): array
    {
        $out = [];
        foreach ($fields as $field) {
            $out[$field] = self::field($field);
        }

        return $out;
    }

    protected static function humanize(string $name): string
    {
        return str_replace('_', ' ', $name);
    }
}
