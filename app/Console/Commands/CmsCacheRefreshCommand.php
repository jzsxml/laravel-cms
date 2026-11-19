<?php

namespace App\Console\Commands;

use App\Services\Admin\AdminCache;
use Illuminate\Console\Command;

class CmsCacheRefreshCommand extends Command
{
    protected $signature = 'cms:cache-refresh';

    protected $description = 'Rebuild admin/site data caches (config, rules, categories)';

    public function handle(AdminCache $cache): int
    {
        $meta = $cache->refresh();
        $this->info('CMS cache refreshed at '.date('Y-m-d H:i:s', (int) ($meta['refreshed_at'] ?? time())));

        return self::SUCCESS;
    }
}
