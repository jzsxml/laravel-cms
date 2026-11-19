<?php

namespace App\Models\Admin;

use App\Models\Concerns\HasUnixTimestamps;
use App\Support\CmsTable;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasUnixTimestamps;

    protected $table;

    protected $fillable = [
        'category', 'admin_id', 'user_id', 'url', 'imagewidth', 'imageheight', 'imagetype',
        'imageframes', 'filename', 'filesize', 'mimetype', 'extparam', 'createtime', 'updatetime',
        'uploadtime', 'storage', 'sha1',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = CmsTable::name('attachment');
        parent::__construct($attributes);
    }
}
