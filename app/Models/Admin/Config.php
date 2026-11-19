<?php

namespace App\Models\Admin;

use App\Support\CmsTable;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    public $timestamps = false;

    protected $table;

    protected $fillable = [
        'name', 'group', 'title', 'tip', 'type', 'visible', 'value', 'content', 'rule', 'extend', 'setting',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = CmsTable::name('config');
        parent::__construct($attributes);
    }
}
