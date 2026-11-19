<?php

namespace App\Models\Admin;

use App\Support\CmsTable;
use Illuminate\Database\Eloquent\Model;

class UserMoneyLog extends Model
{
    public $timestamps = false;

    protected $table;

    protected $fillable = ['member_id', 'money', 'before', 'after', 'memo', 'createtime'];

    public function __construct(array $attributes = [])
    {
        $this->table = CmsTable::name('user_money_log');
        parent::__construct($attributes);
    }
}
