<?php

namespace App\Models\Admin;

use App\Models\Concerns\HasUnixTimestamps;
use App\Support\CmsTable;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasUnixTimestamps;

    protected $table;

    protected $fillable = [
        'order_sn', 'member_id', 'product_type', 'product_id', 'title', 'amount',
        'pay_method', 'status', 'paytime', 'createtime', 'updatetime',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = CmsTable::name('order');
        parent::__construct($attributes);
    }

    public static function generateSn(): string
    {
        return date('YmdHis').substr((string) microtime(true), -6).random_int(10, 99);
    }
}
