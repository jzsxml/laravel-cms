<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlSanitizer
{
    protected static ?HTMLPurifier $purifier = null;

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        if (! config('cms.security.html_purify', true)) {
            return $html;
        }

        return self::purifier()->purify($html);
    }

    protected static function purifier(): HTMLPurifier
    {
        if (self::$purifier !== null) {
            return self::$purifier;
        }

        $cacheDir = storage_path('app/htmlpurifier');
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', $cacheDir);
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube\.com/embed/|player\.vimeo\.com/video/)%');

        self::$purifier = new HTMLPurifier($config);

        return self::$purifier;
    }
}
