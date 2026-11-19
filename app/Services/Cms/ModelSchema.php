<?php

namespace App\Services\Cms;

use App\Models\Admin\CmsModel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ModelSchema
{
    /**
     * @return array<string, mixed>
     */
    public function validateData(CmsModel $model, Request $request): array
    {
        $schema = $model->fieldSchema();
        $rules = [];
        $labels = [];
        foreach ($schema as $field) {
            $name = $field['name'];
            $labels[$name] = $field['label'];
            $rule = ['nullable', 'string', 'max:2000'];
            if ($field['type'] === 'number') {
                $rule = ['nullable', 'numeric'];
            }
            if ($field['type'] === 'image' || $field['type'] === 'text') {
                $rule = ['nullable', 'string', 'max:500'];
            }
            if ($field['required']) {
                $rule = array_filter($rule, fn ($r) => $r !== 'nullable');
                array_unshift($rule, 'required');
            }
            $rules[$name] = $rule;
        }

        $validated = $request->validate($rules);

        return $validated;
    }

    public function encodeData(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * @throws ValidationException
     */
    public function parseFieldsJson(string $raw): string
    {
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            throw ValidationException::withMessages(['fields' => '字段定义必须是 JSON 数组']);
        }
        foreach ($decoded as $i => $row) {
            if (! is_array($row) || empty($row['name'])) {
                throw ValidationException::withMessages(['fields' => '第 '.($i + 1).' 项缺少 name']);
            }
        }

        return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    public function listTitle(CmsModel $model, CmsModelData $row): string
    {
        $data = $row->dataArray();
        foreach ($model->fieldSchema() as $field) {
            if ($field['list'] || $field['type'] === 'text') {
                $val = trim((string) ($data[$field['name']] ?? ''));
                if ($val !== '') {
                    return $val;
                }
            }
        }

        return '#'.$row->getKey();
    }
}
