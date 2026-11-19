<?php

namespace App\Models\Admin;

use App\Models\Concerns\HasUnixTimestamps;
use App\Support\CmsTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasUnixTimestamps;

    protected $table;

    public $timestamps = false;

    protected $fillable = [
        'archive_id', 'member_id', 'member_nickname', 'content', 'ip', 'status',
        'createtime', 'deletetime',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = CmsTable::name('comment');
        parent::__construct($attributes);
    }

    public function archive(): BelongsTo
    {
        return $this->belongsTo(Archive::class, 'archive_id');
    }
}
