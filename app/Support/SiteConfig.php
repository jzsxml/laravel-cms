<?php

namespace App\Support;

use App\Services\Admin\AdminCache;

class SiteConfig
{
    public static function get(string $name, string $default = ''): string
    {
        return app(AdminCache::class)->configValue($name, $default);
    }

    /** @return array<string, string> */
    public static function siteGroup(): array
    {
        $keys = [
            'sitename', 'site_logo', 'site_slogan', 'site_hero_image',
            'site_contact_phone', 'site_contact_email', 'site_icp',
        ];
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = self::get($key);
        }

        return $out;
    }
}
