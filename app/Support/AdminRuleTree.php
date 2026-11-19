<?php

namespace App\Support;

use App\Models\Admin\AuthRule;
use App\Services\Admin\AdminCache;

class AdminRuleTree
{
    /**
     * @return array<int, array{id: int, pid: int, name: string, title: string, children?: array}>
     */
    public static function build(): array
    {
        $rules = app(AdminCache::class)->authRulesModels();

        return self::nest($rules, 0);
    }

    /**
     * @param  array<int, AuthRule>  $rules
     * @return array<int, array{id: int, pid: int, name: string, title: string, children?: array}>
     */
    protected static function nest(array $rules, int $pid): array
    {
        $branch = [];
        foreach ($rules as $rule) {
            if ((int) $rule->pid !== $pid) {
                continue;
            }
            $item = [
                'id' => (int) $rule->id,
                'pid' => (int) $rule->pid,
                'name' => (string) $rule->name,
                'title' => (string) $rule->title,
            ];
            $children = self::nest($rules, (int) $rule->id);
            if ($children !== []) {
                $item['children'] = $children;
            }
            $branch[] = $item;
        }

        return $branch;
    }
}
