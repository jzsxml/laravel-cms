<?php

namespace App\Console\Commands;

use App\Crud\ColumnIntrospector;
use App\Crud\CrudGenerator;
use App\Crud\FieldAnalyzer;
use App\Crud\MenuPublisher;
use App\Crud\NameResolver;
use App\Crud\RoutePublisher;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

class CrudCommand extends Command
{
    protected $signature = 'crud
        {--t|table= : Table name without prefix}
        {--c|controller= : Controller path, e.g. mydir/test}
        {--m|model= : Model class short name}
        {--i|fields= : Comma-separated list fields}
        {--f|force : Overwrite existing files}
        {--d|delete : Delete generated CRUD}
        {--u|menu= : Generate menu rules (1)}
        {--title= : Menu / module title}
        {--r|relation=* : Related table name without prefix}
        {--k|relationforeignkey=* : Foreign keys on main table}
        {--e|relationmodel=* : Related model short names}
        {--imagefield=* : Extra image field suffixes}
        {--selectpagesuffix=* : Extra selectpage suffixes}
        {--editorsuffix=* : Extra editor suffixes}';

    protected $description = 'Generate FastAdmin-style CRUD (controller, model, views, routes, menu)';

    public function handle(): int
    {
        $table = (string) $this->option('table');
        if ($table === '') {
            $this->error('Option --table is required (example: php artisan crud -t test -u 1)');

            return self::FAILURE;
        }

        $suffixOverrides = [];
        if ($images = $this->option('imagefield')) {
            $suffixOverrides['image'] = array_merge(config('cms.crud.field_suffixes.image', []), (array) $images);
        }
        if ($select = $this->option('selectpagesuffix')) {
            $suffixOverrides['selectpage'] = array_merge(config('cms.crud.field_suffixes.selectpage', []), (array) $select);
        }
        if ($editors = $this->option('editorsuffix')) {
            $suffixOverrides['editor'] = array_merge(config('cms.crud.field_suffixes.editor', []), (array) $editors);
        }

        $analyzer = (new FieldAnalyzer)->withSuffixOverrides($suffixOverrides);

        $generator = new CrudGenerator(
            new ColumnIntrospector,
            $analyzer,
            new NameResolver,
            new RoutePublisher(
                config('cms.crud.manifest'),
                config('cms.crud.routes')
            ),
            new MenuPublisher
        );

        $options = [
            'table' => $table,
            'controller' => $this->option('controller'),
            'model' => $this->option('model'),
            'fields' => $this->option('fields'),
            'force' => (bool) $this->option('force'),
            'menu' => (int) $this->option('menu') === 1,
            'title' => $this->option('title'),
            'relations' => $this->buildRelations(),
        ];

        try {
            if ((bool) $this->option('delete')) {
                $generator->delete($options);
                $this->info("CRUD for [{$table}] removed.");

                return self::SUCCESS;
            }

            $paths = $generator->generate($options);
            $this->info("CRUD generated for table [{$table}]:");
            foreach ($paths as $type => $path) {
                if (is_array($path)) {
                    foreach ($path as $p) {
                        $this->line("  [{$type}] {$p}");
                    }
                } else {
                    $this->line("  [{$type}] {$path}");
                }
            }
            if ($options['menu']) {
                $this->info('Menu rules created (auth_rule).');
            }
            $this->comment('Run: php artisan route:clear');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * @return array<int, array{table: string, foreign_key: string, model: string}>
     */
    protected function buildRelations(): array
    {
        $tables = (array) $this->option('relation');
        if ($tables === []) {
            return [];
        }
        $keys = (array) $this->option('relationforeignkey');
        $models = (array) $this->option('relationmodel');
        $relations = [];
        foreach ($tables as $i => $table) {
            $relations[] = [
                'table' => (string) $table,
                'foreign_key' => (string) ($keys[$i] ?? $table.'_id'),
                'model' => (string) ($models[$i] ?? Str::studly($table)),
            ];
        }

        return $relations;
    }
}
