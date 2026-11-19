<?php

namespace App\Support;

use App\Models\Admin\Archive;
use Illuminate\Database\Eloquent\Builder;

class PortalArchiveQuery
{
    public static function published(): Builder
    {
        return Archive::query()
            ->where('status', 'normal')
            ->whereNull('deletetime');
    }

    public static function inCategory(int $categoryId): Builder
    {
        return self::published()->where(function (Builder $q) use ($categoryId) {
            $q->where('category_id', $categoryId)
                ->orWhereRaw("instr(',' || category_ids || ',', ',' || ? || ',') > 0", [$categoryId]);
        });
    }

    public static function search(string $keyword): Builder
    {
        $keyword = trim($keyword);
        $q = self::published()->with('category');
        if ($keyword === '') {
            return $q->whereRaw('0=1');
        }

        $like = '%'.$keyword.'%';

        return $q->where(function (Builder $builder) use ($like) {
            $builder->where('title', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('subtitle', 'like', $like);
        });
    }
}
