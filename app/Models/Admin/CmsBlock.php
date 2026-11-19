<?php

namespace App\Models\Admin;

use App\Models\Concerns\HasUnixTimestamps;
use App\Support\CmsTable;
use Illuminate\Database\Eloquent\Model;

class CmsBlock extends Model
{
    use HasUnixTimestamps;

    protected $table;

    protected $fillable = [
        'name', 'title', 'type', 'content', 'status', 'weigh', 'createtime', 'updatetime',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = CmsTable::name('block');
        parent::__construct($attributes);
    }
}
