<?php

namespace App\Support;

use App\Models\Admin\Config;
use App\Services\Admin\AdminCache;

class SiteSettings
{
    /**
     * 与前台 {@see SiteConfig}、门户主题共用 fa_config 键名。
     *
     * @return array<string, array{title: string, type: string, hint?: string, options?: array<string, string>}>
     */
    public static function definitions(): array
    {
        return [
            'sitename' => ['title' => '网站名称', 'type' => 'string', 'hint' => '顶栏、页脚、浏览器标题'],
            'site_slogan' => ['title' => '网站标语', 'type' => 'string'],
            'site_logo' => ['title' => 'Logo 图片地址', 'type' => 'string', 'hint' => '可填附件 URL 或外链'],
            'site_hero_image' => ['title' => '首页大图地址', 'type' => 'string', 'hint' => 'default 主题首页横幅背景'],
            'site_contact_phone' => ['title' => '联系电话', 'type' => 'string'],
            'site_contact_email' => ['title' => '联系邮箱', 'type' => 'string'],
            'site_icp' => ['title' => '备案号', 'type' => 'string', 'hint' => '页脚 ICP 展示'],
            'portal_theme' => [
                'title' => '门户主题',
                'type' => 'select',
                'options' => [
                    'default' => '默认企业站',
                    'classic' => 'Classic',
                    'cms' => 'CMS 资讯站',
                ],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function values(): array
    {
        $out = [];
        foreach (array_keys(self::definitions()) as $key) {
            $out[$key] = SiteConfig::get($key);
        }

        if ($out['portal_theme'] === '') {
            $out['portal_theme'] = 'default';
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public static function save(array $input): void
    {
        foreach (self::definitions() as $name => $def) {
            $value = trim((string) ($input[$name] ?? ''));
            if ($name === 'portal_theme' && ! isset($def['options'][$value])) {
                $value = 'default';
            }

            Config::query()->updateOrCreate(
                ['name' => $name],
                [
                    'group' => 'site',
                    'title' => $def['title'],
                    'type' => $def['type'] === 'select' ? 'string' : $def['type'],
                    'value' => $value,
                    'visible' => '1',
                    'content' => $name === 'portal_theme'
                        ? collect($def['options'])->map(fn ($label, $k) => $k.'|'.$label)->implode("\n")
                        : '',
                ]
            );
        }

        app(AdminCache::class)->forgetConfig();
    }

    /** @return array<string, string> */
    public static function validationRules(): array
    {
        return [
            'sitename' => 'required|string|max:100',
            'site_slogan' => 'nullable|string|max:255',
            'site_logo' => ['nullable', 'string', 'max:500', 'regex:/^(|\/|https?:\/\/.+)$/i'],
            'site_hero_image' => ['nullable', 'string', 'max:500', 'regex:/^(|\/|https?:\/\/.+)$/i'],
            'site_contact_phone' => 'nullable|string|max:50',
            'site_contact_email' => 'nullable|string|max:100',
            'site_icp' => 'nullable|string|max:100',
            'portal_theme' => 'required|string|in:default,classic,cms',
        ];
    }
}
