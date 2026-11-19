<?php

namespace App\Models\Admin;

use App\Models\Concerns\HasUnixTimestamps;
use App\Support\CmsTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Special extends Model
{
    use HasUnixTimestamps;

    protected $table;

    protected $fillable = [
        'title', 'slug', 'image', 'description', 'content', 'weigh', 'status',
        'createtime', 'updatetime', 'deletetime',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = CmsTable::name('special');
        parent::__construct($attributes);
    }

    public function archives(): BelongsToMany
    {
        return $this->belongsToMany(
            Archive::class,
            CmsTable::name('archive_special'),
            'special_id',
            'archive_id'
        );
    }
}
