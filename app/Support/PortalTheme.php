<?php

namespace App\Support;

class PortalTheme
{
    public static function current(): string
    {
        $raw = SiteConfig::get('portal_theme', 'default');
        $theme = strtolower(preg_replace('/[^a-z0-9_-]/', '', $raw) ?? 'default');

        return $theme !== '' ? $theme : 'default';
    }

    public static function layout(): string
    {
        $theme = self::current();
        $candidate = 'portal.themes.'.$theme.'.layouts.app';
        if (view()->exists($candidate)) {
            return $candidate;
        }

        return 'portal.layouts.app';
    }

    public static function bodyClass(): string
    {
        $theme = self::current();

        return $theme === 'default' ? 'cms-portal-site' : 'cms-portal-site cms-portal-theme-'.$theme;
    }

    public static function view(string $name): string
    {
        $theme = self::current();
        $candidate = 'portal.themes.'.$theme.'.'.$name;
        if (view()->exists($candidate)) {
            return $candidate;
        }

        return 'portal.'.$name;
    }

    /** @return list<string> */
    public static function stylesheets(): array
    {
        $sheets = [asset('css/portal.css')];
        $theme = self::current();
        $themeCss = 'css/portal-'.$theme.'.css';
        if ($theme !== 'default' && is_file(public_path($themeCss))) {
            $sheets[] = asset($themeCss);
        }

        return $sheets;
    }
}
