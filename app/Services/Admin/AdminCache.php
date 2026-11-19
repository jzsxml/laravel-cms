<?php

namespace App\Services\Admin;

use App\Models\Admin\AuthRule;
use App\Models\Admin\Category;
use App\Models\Admin\CmsBlock as BlockModel;
use App\Models\Admin\Config;
use App\Support\CmsBlock;
use Illuminate\Support\Facades\Cache;

class AdminCache
{
    public const KEY_CONFIG = 'cms.admin.config';

    public const KEY_AUTH_RULES = 'cms.admin.auth_rules';

    public const KEY_CATEGORIES = 'cms.admin.categories';

    public const KEY_META = 'cms.admin.cache_meta';

    public function ttl(): int
    {
        return max(60, (int) config('cms.cache.ttl', 3600));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function configMap(): array
    {
        return $this->remember(self::KEY_CONFIG, function () {
            $map = [];
            foreach (Config::query()->get() as $row) {
                $map[(string) $row->name] = $row->toArray();
            }

            return $map;
        });
    }

    public function configValue(string $name, string $default = ''): string
    {
        $row = $this->configMap()[$name] ?? null;
        if (! is_array($row)) {
            return $default;
        }
        $value = $row['value'] ?? null;

        return ($value !== null && $value !== '') ? (string) $value : $default;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function configRow(string $name): ?array
    {
        $row = $this->configMap()[$name] ?? null;

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function authRules(): array
    {
        return $this->remember(self::KEY_AUTH_RULES, function () {
            return AuthRule::query()
                ->orderByDesc('weigh')
                ->orderBy('id')
                ->get()
                ->map(fn (AuthRule $rule) => $rule->getAttributes())
                ->values()
                ->all();
        });
    }

    /**
     * @return array<int, AuthRule>
     */
    public function authRulesModels(): array
    {
        return AuthRule::hydrate($this->authRules())->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function categories(): array
    {
        return $this->remember(self::KEY_CATEGORIES, function () {
            return Category::query()
                ->orderByDesc('weigh')
                ->orderBy('id')
                ->get()
                ->map(fn (Category $row) => $row->getAttributes())
                ->values()
                ->all();
        });
    }

    /**
     * @return array<int, Category>
     */
    public function categoryModels(): array
    {
        return Category::hydrate($this->categories())->all();
    }

    public function forgetConfig(): void
    {
        Cache::forget(self::KEY_CONFIG);
        foreach (Config::query()->pluck('name') as $name) {
            Cache::forget("cms.dict.{$name}");
        }
    }

    public function forgetAuthRules(): void
    {
        Cache::forget(self::KEY_AUTH_RULES);
    }

    public function forgetCategories(): void
    {
        Cache::forget(self::KEY_CATEGORIES);
    }

    public function forgetBlocks(): void
    {
        foreach (BlockModel::query()->pluck('name') as $name) {
            CmsBlock::forget((string) $name);
        }
    }

    public function clear(): void
    {
        $this->forgetConfig();
        $this->forgetAuthRules();
        $this->forgetCategories();
        $this->forgetBlocks();
        Cache::forget(self::KEY_META);
    }

    /**
     * @return array{refreshed_at: int, items: array<int, string>}
     */
    public function refresh(): array
    {
        $this->clear();
        $this->configMap();
        $this->authRules();
        $this->categories();

        $meta = [
            'refreshed_at' => time(),
            'items' => [
                '系统配置',
                '权限菜单规则',
                '栏目分类',
                '字典与区块',
            ],
        ];
        Cache::put(self::KEY_META, $meta, $this->ttl());

        return $meta;
    }

    /**
     * @return array{refreshed_at?: int, items?: array<int, string>}
     */
    public function meta(): array
    {
        $meta = Cache::get(self::KEY_META);

        return is_array($meta) ? $meta : [];
    }

    /**
     * @return array<int, string>
     */
    public function catalog(): array
    {
        return [
            self::KEY_CONFIG => '系统配置（SiteConfig、上传 CDN 等）',
            self::KEY_AUTH_RULES => '后台菜单规则树',
            self::KEY_CATEGORIES => '栏目分类',
            'cms.dict.*' => '配置字典（下拉项）',
            'cms_block_*' => '前台区块 HTML',
        ];
    }

    /**
     * @param  callable(): mixed  $callback
     */
    protected function remember(string $key, callable $callback): mixed
    {
        return Cache::remember($key, $this->ttl(), $callback);
    }
}
