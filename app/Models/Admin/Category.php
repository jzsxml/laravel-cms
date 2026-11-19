<?php

namespace App\Models\Admin;

use App\Models\Concerns\HasUnixTimestamps;
use App\Support\CmsTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasUnixTimestamps;

    protected $table;

    protected $fillable = [
        'pid', 'type', 'name', 'nickname', 'flag', 'image', 'keywords', 'description',
        'weigh', 'status', 'createtime', 'updatetime',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = CmsTable::name('category');
        parent::__construct($attributes);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'pid');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'pid');
    }
}
