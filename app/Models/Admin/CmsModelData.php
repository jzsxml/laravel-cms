<?php

namespace App\Models\Admin;

use App\Models\Concerns\HasUnixTimestamps;
use App\Support\CmsTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsModelData extends Model
{
    use HasUnixTimestamps;

    protected $table;

    protected $fillable = [
        'model_id', 'data', 'status', 'weigh', 'createtime', 'updatetime', 'deletetime',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = CmsTable::name('cms_model_data');
        parent::__construct($attributes);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(CmsModel::class, 'model_id');
    }

    /** @return array<string, mixed> */
    public function dataArray(): array
    {
        $decoded = json_decode((string) ($this->data ?? ''), true);

        return is_array($decoded) ? $decoded : [];
    }
}
