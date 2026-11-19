<?php

namespace App\Crud;

class FieldAnalyzer
{
    public function __construct(protected FieldSuffixRules $suffixRules = new FieldSuffixRules) {}

    /** @param array<string, array<int, string>> $suffixOverrides */
    public function withSuffixOverrides(array $suffixOverrides): self
    {
        return new self(new FieldSuffixRules($suffixOverrides));
    }

    /** @param array{name: string, type_name: string, comment: string, nullable: bool} $column */
    public function formType(array $column): ?string
    {
        $name = $column['name'];
        $type = $column['type_name'];

        if (in_array($name, ['id', 'createtime', 'updatetime', 'deletetime'], true)) {
            return null;
        }

        if ($this->suffixRules->match($name, 'switch')) {
            return 'switch';
        }
        if ($this->suffixRules->match($name, 'image')) {
            return 'image';
        }
        if ($this->suffixRules->match($name, 'images')) {
            return 'images';
        }
        if ($this->suffixRules->match($name, 'file')) {
            return 'file';
        }
        if ($this->suffixRules->match($name, 'editor')) {
            return 'editor';
        }
        if ($this->suffixRules->match($name, 'selectpage')) {
            return 'selectpage';
        }
        if ($this->suffixRules->match($name, 'date')) {
            return 'datetime';
        }
        if ($this->suffixRules->match($name, 'checkbox')) {
            return 'checkbox';
        }
        if ($this->suffixRules->match($name, 'tags')) {
            return 'tags';
        }
        if (in_array($name, ['content', 'description'], true) || $type === 'text') {
            return 'textarea';
        }
        if ($type === 'enum' || $name === 'status') {
            return 'select';
        }
        if (in_array($type, ['integer', 'bigint', 'smallint', 'tinyint'], true)) {
            return 'number';
        }

        return 'text';
    }

    /** @param array{name: string, type_name: string, comment: string, nullable: bool} $column */
    public function listVisible(array $column): bool
    {
        if (in_array($column['name'], ['content', 'deletetime'], true)) {
            return false;
        }

        return true;
    }

    public function selectpageSource(string $field): string
    {
        if (str_ends_with($field, 'category_id')) {
            return 'category';
        }
        if (str_ends_with($field, 'admin_id')) {
            return 'admin';
        }

        return 'category';
    }
}
