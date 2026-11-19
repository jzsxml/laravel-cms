<?php

namespace App\Models\Admin;

use App\Models\Concerns\HasUnixTimestamps;
use App\Support\CmsTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Archive extends Model
{
    use HasUnixTimestamps;

    protected $table;

    protected $fillable = [
        'category_id', 'category_ids', 'admin_id', 'member_id', 'slug', 'title', 'subtitle', 'image', 'description', 'content',
        'keywords', 'flag', 'weigh', 'views', 'pay_type', 'price', 'pay_ratio', 'download_url', 'likes',
        'status', 'publishtime',
        'createtime', 'updatetime', 'deletetime',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = CmsTable::name('archives');
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

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            CmsTable::name('archive_tag'),
            'archive_id',
            'tag_id'
        );
    }

    public function specials(): BelongsToMany
    {
        return $this->belongsToMany(
            Special::class,
            CmsTable::name('archive_special'),
            'archive_id',
            'special_id'
        );
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'archive_id');
    }

    public function hasFlag(string $flag): bool
    {
        $flags = array_filter(array_map('trim', explode(',', (string) $this->flag)));

        return in_array($flag, $flags, true);
    }

    public function portalUrl(): ?string
    {
        $nickname = $this->category?->nickname;
        if ($nickname === null || $nickname === '' || $this->slug === '') {
            return null;
        }

        return url('/c/'.$nickname.'/'.$this->slug);
    }
}
