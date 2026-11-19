<?php

namespace App\Console\Commands;

use App\Addon\AddonManager;
use Illuminate\Console\Command;
use Throwable;

class AddonCommand extends Command
{
    protected $signature = 'addon
        {action : list|make|enable|disable|install|uninstall}
        {name? : Addon name}';

    protected $description = 'Manage local addons (no marketplace)';

    public function handle(AddonManager $manager): int
    {
        $action = strtolower((string) $this->argument('action'));
        $name = (string) ($this->argument('name') ?? '');

        try {
            return match ($action) {
                'list' => $this->listAddons($manager),
                'make' => $this->makeAddon($manager, $name),
                'enable' => $this->wrap($manager->enable(...), $name, 'enabled'),
                'disable' => $this->wrap($manager->disable(...), $name, 'disabled'),
                'install' => $this->wrap($manager->install(...), $name, 'installed'),
                'uninstall' => $this->wrap($manager->uninstall(...), $name, 'uninstalled'),
                default => $this->invalidAction(),
            };
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    protected function listAddons(AddonManager $manager): int
    {
        $rows = $manager->all();
        if ($rows === []) {
            $this->info('No addons in addons/ directory.');

            return self::SUCCESS;
        }
        $this->table(
            ['name', 'title', 'version', 'installed', 'enabled'],
            array_map(fn ($a) => [
                $a['name'],
                $a['title'],
                $a['version'],
                $a['installed'] ? 'yes' : 'no',
                $a['enabled'] ? 'yes' : 'no',
            ], $rows)
        );

        return self::SUCCESS;
    }

    protected function makeAddon(AddonManager $manager, string $name): int
    {
        if ($name === '') {
            $this->error('Usage: php artisan addon make demo');

            return self::FAILURE;
        }
        $path = $manager->scaffold($name);
        $this->info("Addon scaffold created: {$path}");
        $this->comment("Install: php artisan addon install {$name}");

        return self::SUCCESS;
    }

    protected function wrap(callable $fn, string $name, string $done): int
    {
        if ($name === '') {
            $this->error('Addon name required.');

            return self::FAILURE;
        }
        $fn(strtolower($name));
        $this->info("Addon {$name} {$done}.");

        return self::SUCCESS;
    }

    protected function invalidAction(): int
    {
        $this->error('Unknown action. Use: list, make, enable, disable, install, uninstall');

        return self::FAILURE;
    }
}
