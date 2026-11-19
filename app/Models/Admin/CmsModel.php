<?php

namespace App\Models\Admin;

use App\Models\Concerns\HasUnixTimestamps;
use App\Support\CmsTable;
use Illuminate\Database\Eloquent\Model;

class CmsModel extends Model
{
    use HasUnixTimestamps;

    protected $table;

    protected $fillable = [
        'name', 'title', 'fields', 'status', 'weigh', 'createtime', 'updatetime',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = CmsTable::name('cms_model');
        parent::__construct($attributes);
    }

    /** @return list<array{name: string, label: string, type: string, list?: bool, required?: bool, options?: array<string, string>}> */
    public function fieldSchema(): array
    {
        $decoded = json_decode((string) ($this->fields ?? ''), true);
        if (! is_array($decoded)) {
            return [];
        }
        $out = [];
        foreach ($decoded as $row) {
            if (! is_array($row) || empty($row['name'])) {
                continue;
            }
            $out[] = [
                'name' => (string) $row['name'],
                'label' => (string) ($row['label'] ?? $row['name']),
                'type' => (string) ($row['type'] ?? 'text'),
                'list' => (bool) ($row['list'] ?? false),
                'required' => (bool) ($row['required'] ?? false),
                'options' => is_array($row['options'] ?? null) ? $row['options'] : [],
            ];
        }

        return $out;
    }
}
