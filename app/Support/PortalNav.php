<?php

namespace App\Support;

use App\Models\Admin\Category;
use Illuminate\Support\Collection;

class PortalNav
{
    /**
     * @return array<int, array{category: Category, children: array<int, array{category: Category, children: array}>}>
     */
    public static function categoryTree(): array
    {
        $rows = Category::query()
            ->where('type', 'article')
            ->where('status', 'normal')
            ->orderByDesc('weigh')
            ->orderBy('id')
            ->get();

        return self::nest($rows, 0);
    }

    /**
     * @param  Collection<int, Category>  $rows
     * @return array<int, array{category: Category, children: array}>
     */
    protected static function nest(Collection $rows, int $pid): array
    {
        $branch = [];
        foreach ($rows as $row) {
            if ((int) $row->pid !== $pid) {
                continue;
            }
            $branch[] = [
                'category' => $row,
                'children' => self::nest($rows, (int) $row->id),
            ];
        }

        return $branch;
    }
}
