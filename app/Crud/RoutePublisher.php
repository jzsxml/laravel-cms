<?php

namespace App\Crud;

use Illuminate\Support\Facades\File;

class RoutePublisher
{
    public function __construct(
        protected string $manifestPath,
        protected string $routesPath
    ) {
        $this->manifestPath = config('cms.crud.manifest', $manifestPath);
        $this->routesPath = config('cms.crud.routes', $routesPath);
    }

    /**
     * @return array<string, string> slug => controller class
     */
    public function manifest(): array
    {
        if (! File::exists($this->manifestPath)) {
            return [];
        }

        $data = require $this->manifestPath;

        return is_array($data) ? $data : [];
    }

    public function add(string $slug, string $controllerClass): void
    {
        $manifest = $this->manifest();
        $manifest[$slug] = $controllerClass;
        $this->writeManifest($manifest);
        $this->writeRoutes($manifest);
    }

    public function remove(string $slug): void
    {
        $manifest = $this->manifest();
        unset($manifest[$slug]);
        $this->writeManifest($manifest);
        $this->writeRoutes($manifest);
    }

    /**
     * @param  array<string, string>  $manifest
     */
    protected function writeManifest(array $manifest): void
    {
        ksort($manifest);
        File::ensureDirectoryExists(dirname($this->manifestPath));
        $export = var_export($manifest, true);
        File::put($this->manifestPath, "<?php\n\nreturn {$export};\n");
    }

    /**
     * @param  array<string, string>  $manifest
     */
    protected function writeRoutes(array $manifest): void
    {
        $lines = [
            '<?php',
            '',
            'use Illuminate\Support\Facades\Route;',
            '',
            '/** @var array<string, class-string> $generated */',
            '$generated = require dirname(__DIR__).\'/bootstrap/cms/generated_modules.php\';',
            '',
            'foreach ($generated as $slug => $controller) {',
            '    Route::get($slug, [$controller, \'index\']);',
            '    Route::get($slug.\'/add\', [$controller, \'add\']);',
            '    Route::post($slug.\'/add\', [$controller, \'add\']);',
            '    Route::get($slug.\'/edit\', [$controller, \'edit\']);',
            '    Route::post($slug.\'/edit\', [$controller, \'edit\']);',
            '    Route::post($slug.\'/del\', [$controller, \'del\']);',
            '    Route::post($slug.\'/multi\', [$controller, \'multi\']);',
            '    Route::get($slug.\'/recyclebin\', [$controller, \'recyclebin\']);',
            '    Route::post($slug.\'/restore\', [$controller, \'restore\']);',
            '    Route::post($slug.\'/destroy\', [$controller, \'destroy\']);',
            '}',
            '',
        ];
        File::ensureDirectoryExists(dirname($this->routesPath));
        File::put($this->routesPath, implode(PHP_EOL, $lines));
    }
}
