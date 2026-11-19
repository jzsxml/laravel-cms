<?php

namespace App\Models\Admin;

use App\Models\Concerns\HasUnixTimestamps;
use App\Support\CmsTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasUnixTimestamps;

    protected $table;

    protected $fillable = ['name', 'slug', 'weigh', 'createtime', 'updatetime'];

    public function __construct(array $attributes = [])
    {
        $this->table = CmsTable::name('tag');
        parent::__construct($attributes);
    }

    public function archives(): BelongsToMany
    {
        return $this->belongsToMany(Archive::class, CmsTable::name('archive_tag'), 'tag_id', 'archive_id');
    }
}
