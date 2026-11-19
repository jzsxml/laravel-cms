<?php

namespace App\Models\Admin;

use App\Models\Concerns\HasUnixTimestamps;
use App\Support\CmsTable;
use Illuminate\Database\Eloquent\Model;

class AuthGroup extends Model
{
    use HasUnixTimestamps;

    protected $table;

    protected $fillable = ['pid', 'name', 'rules', 'createtime', 'updatetime', 'status'];

    public function __construct(array $attributes = [])
    {
        $this->table = CmsTable::name('auth_group');
        parent::__construct($attributes);
    }

    public function ruleIds(): array
    {
        if ($this->rules === '*' || trim($this->rules) === '*') {
            return ['*'];
        }

        return array_values(array_filter(array_map('intval', explode(',', (string) $this->rules))));
    }
}
