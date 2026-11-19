<?php

namespace App\Support;

class AdminTableFilters
{
    /**
     * @param  array<int, string>  $listFields
     * @return array<string, array{label: string, type: string, defaultOp: string, options?: array<string, string>}>
     */
    public static function fromListFields(array $listFields, array $skip = ['id']): array
    {
        $out = [];
        foreach ($listFields as $field) {
            if (in_array($field, $skip, true)) {
                continue;
            }
            $out[$field] = self::metaFor($field);
        }

        return $out;
    }

    /**
     * @return array{label: string, type: string, defaultOp: string, options?: array<string, string>}
     */
    public static function metaFor(string $field): array
    {
        $label = AdminLang::field($field);

        if ($field === 'status') {
            return [
                'label' => $label,
                'type' => 'select',
                'defaultOp' => '=',
                'options' => ['normal' => '正常', 'hidden' => '隐藏'],
            ];
        }

        if ($field === 'switch') {
            return [
                'label' => $label,
                'type' => 'select',
                'defaultOp' => '=',
                'options' => ['1' => '开', '0' => '关'],
            ];
        }

        if (str_ends_with($field, 'time')) {
            return [
                'label' => $label,
                'type' => 'daterange',
                'defaultOp' => 'RANGE',
            ];
        }

        if (preg_match('/_id$|^(weigh|filesize|pid)$/i', $field)) {
            return [
                'label' => $label,
                'type' => 'number',
                'defaultOp' => '=',
            ];
        }

        return [
            'label' => $label,
            'type' => 'text',
            'defaultOp' => 'LIKE',
        ];
    }
}
