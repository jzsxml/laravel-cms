<?php

namespace App\Models\Admin;

use App\Models\Concerns\HasUnixTimestamps;
use App\Support\CmsTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Test extends Model
{
    use HasUnixTimestamps;

    protected $table;

    protected $fillable = [
        'admin_id', 'category_id', 'title', 'image', 'images', 'weigh', 'switch', 'status', 'content',
        'createtime', 'updatetime', 'deletetime',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = CmsTable::name('test');
        parent::__construct($attributes);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
