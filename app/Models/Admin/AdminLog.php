<?php

namespace App\Models\Admin;

use App\Support\CmsTable;
use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    public $timestamps = false;

    protected $table;

    protected $fillable = [
        'admin_id', 'username', 'url', 'title', 'content', 'ip', 'useragent', 'createtime',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = CmsTable::name('admin_log');
        parent::__construct($attributes);
    }
}
