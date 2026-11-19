<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FastAdmin-compatible table prefix
    |--------------------------------------------------------------------------
    */
    'table_prefix' => env('CMS_TABLE_PREFIX', 'fa_'),

    /*
    |--------------------------------------------------------------------------
    | Admin URL prefix (no leading slash)
    |--------------------------------------------------------------------------
    */
    'admin_path' => env('CMS_ADMIN_PATH', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Admin UI locale (FastAdmin-style lang pack)
    |--------------------------------------------------------------------------
    */
    'admin_locale' => env('CMS_ADMIN_LOCALE', 'zh_CN'),
    'admin_locales' => ['zh_CN', 'en'],
    'admin_locale_session' => 'cms_admin_locale',

    /*
    |--------------------------------------------------------------------------
    | Session key for admin auth
    |--------------------------------------------------------------------------
    */
    'session_key' => env('CMS_SESSION_KEY', 'cms_admin'),

    /*
    |--------------------------------------------------------------------------
    | CRUD generator
    |--------------------------------------------------------------------------
    */
    'crud' => [
        'manifest' => base_path('bootstrap/cms/generated_modules.php'),
        'routes' => base_path('routes/admin_crud.php'),
        'protected_tables' => [
            'admin', 'admin_log', 'auth_group', 'auth_group_access', 'auth_rule',
            'attachment', 'archives', 'archive_tag', 'archive_special', 'comment', 'special', 'block', 'page', 'tag', 'order', 'user_money_log', 'cms_model', 'cms_model_data', 'config', 'category', 'user', 'user_group', 'user_rule',
        ],
        'recycle_field' => 'deletetime',
        'field_suffixes' => [
            'checkbox' => ['list', 'data'],
            'image' => ['image', 'img'],
            'images' => ['images'],
            'file' => ['file'],
            'date' => ['time'],
            'switch' => ['switch'],
            'selectpage' => ['_id'],
            'editor' => ['content'],
            'tags' => ['tags'],
        ],
        'selectpage_sources' => [
            'category' => \App\Models\Admin\Category::class,
            'admin' => \App\Models\Admin\Admin::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Local addons (no remote marketplace)
    |--------------------------------------------------------------------------
    */
    'addons' => [
        'path' => base_path('addons'),
        'enabled' => base_path('bootstrap/cms/enabled_addons.php'),
        'installed' => base_path('bootstrap/cms/installed_addons.php'),
        'provider_class' => 'AddonServiceProvider',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin data cache
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'ttl' => (int) env('CMS_ADMIN_CACHE_TTL', 3600),
    ],

    /*
    |--------------------------------------------------------------------------
    | API / member
    |--------------------------------------------------------------------------
    */
    'api' => [
        'token_header' => 'HTTP_X_CMS_TOKEN',
        'token_param' => 'token',
        /** 无短信验证码时是否开放「用户名+手机号」重置密码（生产环境应保持 false） */
        'allow_password_reset' => env('CMS_API_ALLOW_PASSWORD_RESET', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | 前台会员中心（Web Session）
    |--------------------------------------------------------------------------
    */
    'member_path' => env('CMS_MEMBER_PATH', 'user'),
    'member_session_key' => env('CMS_MEMBER_SESSION_KEY', 'cms_member'),

    'portal' => [
        'reserved_slugs' => [],
        /** 付费内容下载字段为外链时是否 302 跳转（注意钓鱼风险） */
        'allow_external_download_redirect' => env('CMS_ALLOW_EXTERNAL_DOWNLOAD', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin online command whitelist (local addon/dev tooling)
    |--------------------------------------------------------------------------
    */
    'commands_whitelist' => [
        'migrate:status',
        'route:list',
        'addon list',
        'cms:menu-sync',
    ],

    'upload' => [
        /** @see config/filesystems.php disks: public, oss, s3, … */
        'disk' => env('CMS_UPLOAD_DISK', 'public'),
        /** 对象存储盘名（删除附件时按 URL 前缀匹配） */
        'cloud_disks' => ['oss', 's3'],
        'chunk_size' => 2 * 1024 * 1024,
        'chunk_disk' => 'local',
        'chunk_path' => 'chunks',
        'allowed_extensions' => [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', 'mp4', 'mp3', 'txt', 'csv',
        ],
        'blocked_extensions' => [
            'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8', 'exe', 'bat', 'cmd', 'sh', 'js', 'html', 'htm', 'htaccess',
        ],
        'allowed_mimes' => [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
            'application/pdf', 'application/zip', 'application/x-zip-compressed',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain', 'text/csv', 'video/mp4', 'audio/mpeg', 'audio/mp3',
        ],
    ],

    'payment' => [
        'driver' => env('CMS_PAYMENT_DRIVER', 'balance'),
        'allow_mock_recharge' => env('CMS_ALLOW_MOCK_RECHARGE', false),
        'mock_recharge_amount' => (float) env('CMS_MOCK_RECHARGE_AMOUNT', 100),
        'mock_recharge_max' => (float) env('CMS_MOCK_RECHARGE_MAX', 1000),
    ],

    'sms' => [
        'driver' => env('CMS_SMS_DRIVER', 'log'),
        'aliyun' => [
            'access_key' => env('CMS_ALIYUN_SMS_KEY', ''),
            'access_secret' => env('CMS_ALIYUN_SMS_SECRET', ''),
            'sign' => env('CMS_ALIYUN_SMS_SIGN', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 安全：富文本净化、CSP、登录限流
    |--------------------------------------------------------------------------
    */
    'security' => [
        'html_purify' => env('CMS_HTML_PURIFY', true),
        'csp_enabled' => env('CMS_CSP_ENABLED', true),
        'csp' => env('CMS_CSP_POLICY', implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "object-src 'none'",
            "script-src 'self' https://cdn.jsdelivr.net",
            "style-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'",
            "img-src 'self' data: https: blob:",
            "font-src 'self' https://cdn.jsdelivr.net data:",
            "connect-src 'self'",
            "frame-src https://www.youtube.com https://player.vimeo.com",
        ])),
        'login' => [
            'max_attempts' => (int) env('CMS_LOGIN_MAX_ATTEMPTS', 5),
            'decay_minutes' => (int) env('CMS_LOGIN_DECAY_MINUTES', 1),
        ],
    ],

];