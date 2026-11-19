<?php

namespace App\Crud;

use Illuminate\Support\Str;

class NameResolver
{
    public function tableName(string $table, string $prefix): string
    {
        $table = strtolower($table);
        if (Str::startsWith($table, $prefix)) {
            return $table;
        }

        return $prefix.$table;
    }

    public function baseName(string $table, string $prefix): string
    {
        $table = strtolower($table);
        if (Str::startsWith($table, $prefix)) {
            $table = Str::after($table, $prefix);
        }

        return $table;
    }

    public function modelClass(string $baseName, ?string $modelOption = null): string
    {
        $short = $modelOption ? Str::studly($modelOption) : Str::studly($baseName);

        return 'App\\Models\\Admin\\'.$short;
    }

    /**
     * @return array{class: string, slug: string, sub_namespace: string}
     */
    public function controller(string $baseName, ?string $controllerOption = null): array
    {
        if ($controllerOption) {
            $path = str_replace('\\', '/', $controllerOption);
            $path = trim($path, '/');
            $segments = explode('/', $path);
            $short = Str::studly(array_pop($segments));
            $sub = implode('\\', array_map(fn ($s) => Str::studly($s), $segments));
            $class = 'App\\Http\\Controllers\\Admin\\'
                .($sub ? $sub.'\\' : '')
                .$short.'Controller';
            $slug = strtolower(str_replace('\\', '/', ($sub ? str_replace('\\', '/', $sub).'/' : '').$short));

            return ['class' => $class, 'slug' => $slug, 'sub_namespace' => $sub];
        }

        if (str_contains($baseName, '_')) {
            $segments = explode('_', $baseName);
            $short = Str::studly(array_pop($segments));
            $sub = implode('\\', array_map(fn ($s) => Str::studly($s), $segments));
            $class = 'App\\Http\\Controllers\\Admin\\'.$sub.'\\'.$short.'Controller';
            $slug = strtolower(str_replace('\\', '/', $sub.'/'.$short));

            return ['class' => $class, 'slug' => $slug, 'sub_namespace' => $sub];
        }

        $short = Str::studly($baseName);

        return [
            'class' => 'App\\Http\\Controllers\\Admin\\'.$short.'Controller',
            'slug' => strtolower($baseName),
            'sub_namespace' => '',
        ];
    }
}
