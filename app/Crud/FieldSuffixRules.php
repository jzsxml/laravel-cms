<?php

namespace App\Crud;

class FieldSuffixRules
{
    /** @param array<string, array<int, string>> $overrides */
    public function __construct(protected array $overrides = []) {}

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $defaults = config('cms.crud.field_suffixes', []);

        return array_merge($defaults, $this->overrides);
    }

    public function match(string $fieldName, string $ruleKey): bool
    {
        $suffixes = $this->rules()[$ruleKey] ?? [];
        foreach ($suffixes as $suffix) {
            if ($suffix !== '' && str_ends_with($fieldName, $suffix)) {
                return true;
            }
            if ($fieldName === $suffix) {
                return true;
            }
        }

        return false;
    }
}
