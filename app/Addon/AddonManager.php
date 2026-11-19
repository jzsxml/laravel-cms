<?php

namespace App\Addon;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class AddonManager
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $path = (string) config('cms.addons.path');
        if (! File::isDirectory($path)) {
            return [];
        }

        $addons = [];
        foreach (File::directories($path) as $dir) {
            $name = strtolower(basename($dir));
            $meta = $this->readMeta($dir);
            if ($meta === null) {
                continue;
            }
            $addons[] = array_merge($meta, [
                'name' => $name,
                'path' => $dir,
                'enabled' => $this->isEnabled($name),
                'installed' => $this->isInstalled($name),
            ]);
        }

        usort($addons, fn ($a, $b) => strcmp((string) $a['name'], (string) $b['name']));

        return $addons;
    }

    public function readMeta(string $dir): ?array
    {
        $ini = $dir.DIRECTORY_SEPARATOR.'info.ini';
        if (! File::exists($ini)) {
            return null;
        }
        $parsed = parse_ini_file($ini, true, INI_SCANNER_TYPED);
        if (! is_array($parsed) || empty($parsed['name'])) {
            $parsed = $parsed ?: [];
            $parsed['name'] = basename($dir);
        }

        $provider = (string) ($parsed['provider'] ?? '');
        if ($provider === '') {
            $ns = (string) ($parsed['namespace'] ?? 'Addons\\'.Str::studly(basename($dir)));
            $provider = $ns.'\\'.config('cms.addons.provider_class', 'AddonServiceProvider');
        }

        return [
            'title' => (string) ($parsed['title'] ?? Str::headline(basename($dir))),
            'intro' => (string) ($parsed['intro'] ?? ''),
            'version' => (string) ($parsed['version'] ?? '1.0.0'),
            'author' => (string) ($parsed['author'] ?? ''),
            'provider' => $provider,
            'namespace' => (string) ($parsed['namespace'] ?? ''),
        ];
    }

    public function enable(string $name): void
    {
        $name = $this->validateAddonName($name);
        $this->assertExists($name);
        $enabled = $this->enabledNames();
        if (! in_array($name, $enabled, true)) {
            $enabled[] = $name;
            $this->writePhpArray(config('cms.addons.enabled'), $enabled);
        }
    }

    public function disable(string $name): void
    {
        $name = $this->validateAddonName($name);
        $enabled = array_values(array_filter(
            $this->enabledNames(),
            fn ($n) => $n !== $name
        ));
        $this->writePhpArray(config('cms.addons.enabled'), $enabled);
    }

    public function install(string $name): void
    {
        $name = $this->validateAddonName($name);
        $dir = $this->addonPath($name);
        $this->assertExists($name);

        $migrations = $dir.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations';
        if (File::isDirectory($migrations)) {
            Artisan::call('migrate', [
                '--path' => str_replace(base_path().DIRECTORY_SEPARATOR, '', $migrations),
                '--force' => true,
            ]);
        }

        $installed = $this->installedNames();
        if (! in_array($name, $installed, true)) {
            $installed[] = $name;
            $this->writePhpArray(config('cms.addons.installed'), $installed);
        }

        $this->enable($name);
    }

    public function uninstall(string $name): void
    {
        $name = $this->validateAddonName($name);
        $this->disable($name);
        $installed = array_values(array_filter(
            $this->installedNames(),
            fn ($n) => $n !== $name
        ));
        $this->writePhpArray(config('cms.addons.installed'), $installed);
    }

    /**
     * @return array<int, string>
     */
    public function enabledNames(): array
    {
        return $this->loadManifest(config('cms.addons.enabled'));
    }

    /**
     * @return array<int, string>
     */
    public function installedNames(): array
    {
        return $this->loadManifest(config('cms.addons.installed'));
    }

    public function isEnabled(string $name): bool
    {
        return in_array(strtolower($name), $this->enabledNames(), true);
    }

    public function isInstalled(string $name): bool
    {
        return in_array(strtolower($name), $this->installedNames(), true);
    }

    public function registerEnabledProviders(): void
    {
        foreach ($this->enabledNames() as $name) {
            $dir = $this->addonPath($name);
            if (! File::isDirectory($dir)) {
                continue;
            }
            $meta = $this->readMeta($dir);
            if (! $meta) {
                continue;
            }
            $providerFile = $dir.DIRECTORY_SEPARATOR.'AddonServiceProvider.php';
            if (File::exists($providerFile)) {
                require_once $providerFile;
            }
            $class = $meta['provider'];
            if (class_exists($class)) {
                app()->register($class);
            }
        }
    }

    public function scaffold(string $name): string
    {
        $name = strtolower(Str::slug($name, ''));
        if ($name === '') {
            throw new RuntimeException('Invalid addon name');
        }
        $dir = $this->addonPath($name);
        if (File::isDirectory($dir)) {
            throw new RuntimeException("Addon already exists: {$name}");
        }

        $studly = Str::studly($name);
        $namespace = "Addons\\{$studly}";

        File::ensureDirectoryExists($dir.'/database/migrations');
        File::put($dir.'/info.ini', implode("\n", [
            'name = '.$name,
            'title = '.$studly.' plugin',
            'intro = Local addon scaffold',
            'version = 1.0.0',
            'author = local',
            'namespace = '.$namespace,
            'provider = '.$namespace.'\\AddonServiceProvider',
            '',
        ]));

        $provider = <<<PHP
<?php

namespace {$namespace};

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AddonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        \$admin = config('cms.admin_path', 'admin');
        Route::middleware(['web', 'admin.auth', 'admin.permission'])
            ->prefix(\$admin.'/addon/{$name}')
            ->group(__DIR__.'/routes.php');
    }
}
PHP;
        File::put($dir.'/AddonServiceProvider.php', $provider);
        File::put($dir.'/routes.php', "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\nRoute::get('/', fn () => response()->json(['code' => 1, 'msg' => '{$name} ok']));\n");

        return $dir;
    }

    protected function addonPath(string $name): string
    {
        return rtrim((string) config('cms.addons.path'), '/\\').DIRECTORY_SEPARATOR.strtolower($name);
    }

    protected function validateAddonName(string $name): string
    {
        $name = strtolower(trim($name));
        if (! preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $name)) {
            throw new RuntimeException('Invalid addon name');
        }

        return $name;
    }

    protected function assertExists(string $name): void
    {
        $name = $this->validateAddonName($name);
        $base = realpath((string) config('cms.addons.path'));
        $dir = realpath($this->addonPath($name));
        if ($base === false || $dir === false || ! str_starts_with($dir, $base.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException("Addon not found: {$name}");
        }
        if (! File::isDirectory($this->addonPath($name))) {
            throw new RuntimeException("Addon not found: {$name}");
        }
        if ($this->readMeta($this->addonPath($name)) === null) {
            throw new RuntimeException("Addon info.ini missing: {$name}");
        }
    }

    /**
     * @return array<int, string>
     */
    protected function loadManifest(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }

        $this->invalidateManifestCache($path);
        $data = include $path;

        return is_array($data) ? array_values(array_map('strtolower', $data)) : [];
    }

    /**
     * @param  array<int, string>  $items
     */
    protected function writePhpArray(string $path, array $items): void
    {
        $items = array_values(array_unique(array_map('strtolower', $items)));
        sort($items);
        File::ensureDirectoryExists(dirname($path));
        File::put($path, "<?php\n\nreturn ".var_export($items, true).";\n");
        $this->invalidateManifestCache($path);
    }

    protected function invalidateManifestCache(string $path): void
    {
        clearstatcache(true, $path);
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($path, true);
        }
    }
}
