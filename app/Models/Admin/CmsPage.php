<?php

namespace App\Models\Admin;

use App\Models\Concerns\HasUnixTimestamps;
use App\Support\CmsTable;
use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    use HasUnixTimestamps;

    protected $table;

    protected $fillable = [
        'slug', 'title', 'template', 'image', 'description', 'content', 'keywords',
        'flag', 'weigh', 'views', 'pay_type', 'price', 'pay_ratio', 'download_url',
        'status', 'createtime', 'updatetime', 'deletetime',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = CmsTable::name('page');
        parent::__construct($attributes);
    }

    public function hasFlag(string $flag): bool
    {
        $flags = array_filter(array_map('trim', explode(',', (string) $this->flag)));

        return in_array($flag, $flags, true);
    }

    public function portalUrl(): string
    {
        return url('/p/'.$this->slug);
    }
}
