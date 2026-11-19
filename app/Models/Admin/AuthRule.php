<?php

namespace App\Models\Admin;

use App\Models\Concerns\HasUnixTimestamps;
use App\Support\CmsTable;
use Illuminate\Database\Eloquent\Model;

class AuthRule extends Model
{
    use HasUnixTimestamps;

    protected $table;

    protected $fillable = [
        'type', 'pid', 'name', 'title', 'icon', 'url', 'condition', 'remark',
        'ismenu', 'menutype', 'extend', 'py', 'pinyin', 'createtime', 'updatetime', 'weigh', 'status',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = CmsTable::name('auth_rule');
        parent::__construct($attributes);
    }
}
