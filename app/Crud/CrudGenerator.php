<?php

namespace App\Crud;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class CrudGenerator
{
    public function __construct(
        protected ColumnIntrospector $columns,
        protected FieldAnalyzer $fields,
        protected NameResolver $names,
        protected RoutePublisher $routes,
        protected MenuPublisher $menu
    ) {}

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, string> generated paths
     */
    public function generate(array $options): array
    {
        $prefix = config('cms.table_prefix', 'fa_');
        $base = $this->names->baseName((string) $options['table'], $prefix);
        $this->guardProtected($base);

        $fullTable = $this->names->tableName((string) $options['table'], $prefix);
        $columnDefs = $this->columns->columns($fullTable);
        if ($columnDefs === []) {
            throw new RuntimeException("Table {$fullTable} not found or has no columns.");
        }

        $controller = $this->names->controller($base, $options['controller'] ?? null);
        $modelClass = $this->names->modelClass($base, $options['model'] ?? null);
        $modelShort = class_basename($modelClass);

        $recycleField = config('cms.crud.recycle_field', 'deletetime');
        $softDelete = $this->columns->hasColumn($fullTable, $recycleField);

        $listFields = $this->resolveListFields($columnDefs, $options['fields'] ?? null);
        $formFields = $this->buildFormFields($columnDefs);
        if (array_key_exists('content', $formFields)) {
            $formFields['content'] = 'editor:content';
        }

        $title = (string) ($options['title'] ?? Str::headline($base));

        $paths = [];
        $paths['model'] = $this->writeModel(
            $modelClass,
            $modelShort,
            $base,
            $columnDefs,
            (array) ($options['relations'] ?? [])
        );
        $paths['controller'] = $this->writeController(
            $controller,
            $modelClass,
            $modelShort,
            $title,
            $listFields,
            $formFields,
            $softDelete,
            $recycleField,
            (bool) ($options['force'] ?? false)
        );
        $paths['views'] = $this->writeViews($controller['slug'], $listFields, $formFields, $softDelete);
        $paths['lang'] = $this->writeLang($controller['slug'], $title);
        $paths['js'] = $this->writeJsStub($controller['slug']);

        $this->routes->add($controller['slug'], $controller['class']);

        if (! empty($options['menu'])) {
            $actions = ['index', 'add', 'edit', 'del', 'multi'];
            if ($softDelete) {
                $actions = array_merge($actions, ['recyclebin', 'restore', 'destroy']);
            }
            $this->menu->publish($controller['slug'], $title, $actions, $softDelete);
        }

        return $paths;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function delete(array $options): void
    {
        $prefix = config('cms.table_prefix', 'fa_');
        $base = $this->names->baseName((string) $options['table'], $prefix);
        $controller = $this->names->controller($base, $options['controller'] ?? null);
        $modelClass = $this->names->modelClass($base, $options['model'] ?? null);
        $modelShort = class_basename($modelClass);

        $controllerPath = $this->controllerPath($controller['class']);
        $modelPath = app_path('Models/Admin/'.$modelShort.'.php');
        $viewDir = resource_path('views/admin/crud/'.$controller['slug']);
        $langPath = lang_path('zh_CN/admin/'.$controller['slug'].'.php');
        $jsPath = resource_path('js/admin/'.$controller['slug'].'.js');

        foreach ([$controllerPath, $modelPath, $langPath, $jsPath] as $file) {
            if (File::exists($file)) {
                File::delete($file);
            }
        }
        if (File::isDirectory($viewDir)) {
            File::deleteDirectory($viewDir);
        }

        $this->routes->remove($controller['slug']);
        if (! empty($options['menu'])) {
            $this->menu->publish($controller['slug'], '', [], false, true);
        }
    }

    protected function guardProtected(string $base): void
    {
        $protected = config('cms.crud.protected_tables', []);
        if (in_array($base, $protected, true)) {
            throw new RuntimeException("Table {$base} is protected and cannot be used with CRUD.");
        }
    }

    /**
     * @param  array<int, array{name: string, type_name: string, comment: string, nullable: bool}>  $columnDefs
     * @return array<int, string>
     */
    protected function resolveListFields(array $columnDefs, ?string $fieldsOption): array
    {
        if ($fieldsOption) {
            return array_values(array_filter(array_map('trim', explode(',', $fieldsOption))));
        }

        $fields = [];
        foreach ($columnDefs as $column) {
            if ($this->fields->listVisible($column)) {
                $fields[] = $column['name'];
            }
        }

        return $fields;
    }

    /**
     * @param  array<int, array{name: string, type_name: string, comment: string, nullable: bool}>  $columnDefs
     * @return array<string, string>
     */
    protected function buildFormFields(array $columnDefs): array
    {
        $form = [];
        foreach ($columnDefs as $column) {
            $type = $this->fields->formType($column);
            if ($type === null) {
                continue;
            }
            $form[$column['name']] = $type.':'.$column['comment'];
        }

        return $form;
    }

    /**
     * @param  array<int, array{name: string, type_name: string, comment: string, nullable: bool}>  $columnDefs
     */
    /**
     * @param  array<int, array{table: string, foreign_key: string, model: string}>  $relations
     */
    protected function writeModel(string $modelClass, string $modelShort, string $base, array $columnDefs, array $relations = []): string
    {
        $fillable = [];
        foreach ($columnDefs as $column) {
            if (in_array($column['name'], ['id', 'deletetime'], true)) {
                continue;
            }
            $fillable[] = $column['name'];
        }

        $stub = $this->stub('model.stub');
        $content = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ table }}', '{{ fillable }}', '{{ relations }}', '{{ relation_imports }}'],
            [
                'App\\Models\\Admin',
                $modelShort,
                config('cms.table_prefix', 'fa_').$base,
                $this->exportFillable($fillable),
                $this->exportRelationMethods($relations),
                $this->exportRelationImports($relations),
            ],
            $stub
        );

        $path = app_path('Models/Admin/'.$modelShort.'.php');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        return $path;
    }

    /**
     * @param  array<int, string>  $listFields
     * @param  array<string, string>  $formFields
     */
    protected function writeController(
        array $controller,
        string $modelClass,
        string $modelShort,
        string $title,
        array $listFields,
        array $formFields,
        bool $softDelete,
        string $recycleField,
        bool $force
    ): string {
        $path = $this->controllerPath($controller['class']);
        if (File::exists($path) && ! $force) {
            throw new RuntimeException("Controller already exists: {$path} (use --force)");
        }

        $ns = Str::beforeLast($controller['class'], '\\');
        $classShort = class_basename($controller['class']);

        $searchFields = array_values(array_filter(
            $listFields,
            fn ($f) => in_array($f, ['title', 'name', 'username'], true) || str_contains($f, 'title')
        ));

        $stub = $this->stub('controller.stub');
        $content = str_replace(
            [
                '{{ namespace }}',
                '{{ class }}',
                '{{ model_import }}',
                '{{ model_class }}',
                '{{ title }}',
                '{{ list_fields }}',
                '{{ search_fields }}',
                '{{ form_fields }}',
                '{{ soft_delete }}',
                '{{ soft_delete_column }}',
            ],
            [
                $ns,
                $classShort,
                $modelClass,
                $modelShort,
                $title,
                $this->exportStrings($listFields),
                $this->exportStrings($searchFields),
                $this->exportAssoc($formFields),
                $softDelete ? 'true' : 'false',
                $recycleField,
            ],
            $stub
        );

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        return $path;
    }

    /**
     * @param  array<int, string>  $listFields
     * @param  array<string, string>  $formFields
     * @return array<int, string>
     */
    protected function writeViews(string $slug, array $listFields, array $formFields, bool $softDelete): array
    {
        $dir = resource_path('views/admin/crud/'.$slug);
        File::ensureDirectoryExists($dir);

        $paths = [];
        $rowCells = $this->bladeRowCells($listFields, withActions: true);
        $paths[] = $this->renderView('index.stub', $dir.'/index.blade.php', [
            '{{ list_headers }}' => $this->bladeTableHeaders($listFields),
            '{{ list_row_cells }}' => $rowCells,
        ]);
        $paths[] = $this->renderView('form.stub', $dir.'/form.blade.php', [
            '{{ form_inputs }}' => $this->bladeFormInputs($formFields),
        ]);
        if ($softDelete) {
            $paths[] = $this->renderView('recyclebin.stub', $dir.'/recyclebin.blade.php', [
                '{{ list_headers }}' => $this->bladeTableHeaders($listFields),
                '{{ list_row_cells }}' => $this->bladeRowCells($listFields, withActions: false, recycle: true),
            ]);
        }

        return $paths;
    }

    protected function writeLang(string $slug, string $title): string
    {
        $path = lang_path('zh_CN/admin/'.$slug.'.php');
        File::ensureDirectoryExists(dirname($path));
        $content = "<?php\n\nreturn [\n    'title' => ".var_export($title, true).",\n];\n";
        File::put($path, $content);

        return $path;
    }

    protected function writeJsStub(string $slug): string
    {
        $path = resource_path('js/admin/'.$slug.'.js');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $this->stub('backend.js.stub'));

        return $path;
    }

    protected function controllerPath(string $class): string
    {
        $relative = Str::after($class, 'App\\Http\\Controllers\\');
        $relative = str_replace('\\', '/', $relative);

        return app_path('Http/Controllers/'.$relative.'.php');
    }

    protected function stub(string $name): string
    {
        $path = base_path('stubs/crud/'.$name);
        if (! File::exists($path)) {
            throw new RuntimeException("Missing stub: {$path}");
        }

        return File::get($path);
    }

    /**
     * @param  array<string, string>  $replacements
     */
    protected function renderView(string $stubName, string $target, array $replacements): string
    {
        $content = str_replace(array_keys($replacements), array_values($replacements), $this->stub($stubName));
        File::put($target, $content);

        return $target;
    }

    /** @param array<int, string> $fields */
    protected function exportFillable(array $fields): string
    {
        return "[\n        '".implode("', '", $fields)."',\n    ]";
    }

    /** @param array<int, string> $fields */
    protected function exportStrings(array $fields): string
    {
        if ($fields === []) {
            return '[]';
        }

        return "['".implode("', '", $fields)."']";
    }

    /** @param array<string, string> $assoc */
    protected function exportAssoc(array $assoc): string
    {
        if ($assoc === []) {
            return '[]';
        }
        $lines = [];
        foreach ($assoc as $k => $v) {
            $lines[] = "        '{$k}' => '{$v}',";
        }

        return "[\n".implode("\n", $lines)."\n    ]";
    }

    /** @param array<int, string> $fields */
    protected function bladeTableHeaders(array $fields): string
    {
        return implode('', array_map(fn ($f) => "<th>{$f}</th>", $fields)).'<th>操作</th>';
    }

    /**
     * @param  array<int, string>  $fields
     */
    protected function bladeRowCells(array $fields, bool $withActions = true, bool $recycle = false): string
    {
        $parts = array_map(fn ($f) => '<td>${row.'.$f.' ?? ""}</td>', $fields);
        if ($withActions) {
            $parts[] = '<td>'
                .'<a class="btn btn-sm btn-outline-primary me-1" href="${base}/edit?ids=${row.id}">编辑</a>'
                .'<button type="button" class="btn btn-sm btn-outline-danger" data-del="${row.id}">删除</button>'
                .'</td>';
        }
        if ($recycle) {
            $parts[] = '<td><button type="button" class="btn btn-sm btn-outline-success" data-restore="${row.id}">还原</button></td>';
        }

        return implode("\n            ", $parts);
    }

    /** @param array<string, string> $formFields */
    protected function bladeFormInputs(array $formFields): string
    {
        $html = '';
        foreach ($formFields as $name => $meta) {
            [$type, $label] = array_pad(explode(':', $meta, 2), 2, $name);
            $label = $label ?: $name;
            $value = "{{ old('{$name}', \$record->{$name} ?? '') }}";
            if ($type === 'textarea') {
                $html .= "<div class=\"mb-3\"><label class=\"form-label\">{$label}</label>"
                    ."<textarea name=\"{$name}\" class=\"form-control\" rows=\"4\">{$value}</textarea></div>";
            } elseif ($type === 'switch') {
                $html .= '<div class="mb-3 form-check">'
                    ."<input type=\"hidden\" name=\"{$name}\" value=\"0\">"
                    ."<input type=\"checkbox\" name=\"{$name}\" value=\"1\" class=\"form-check-input\" id=\"f_{$name}\" @checked(old('{$name}', \$record->{$name} ?? 0))>"
                    ."<label class=\"form-check-label\" for=\"f_{$name}\">{$label}</label></div>";
            } else {
                $inputType = $type === 'number' ? 'number' : 'text';
                $html .= "<div class=\"mb-3\"><label class=\"form-label\">{$label}</label>"
                    ."<input type=\"{$inputType}\" name=\"{$name}\" class=\"form-control\" value=\"{$value}\"></div>";
            }
        }

        return $html;
    }

    /**
     * @param  array<int, array{table: string, foreign_key: string, model: string}>  $relations
     */
    protected function exportRelationMethods(array $relations): string
    {
        if ($relations === []) {
            return '';
        }
        $methods = '';
        foreach ($relations as $rel) {
            $method = Str::camel($rel['table']);
            $related = $rel['model'];
            $fk = $rel['foreign_key'];
            $methods .= "\n    public function {$method}(): BelongsTo\n";
            $methods .= "    {\n        return \$this->belongsTo({$related}::class, '{$fk}');\n    }\n";
        }

        return $methods;
    }

    /**
     * @param  array<int, array{table: string, foreign_key: string, model: string}>  $relations
     */
    protected function exportRelationImports(array $relations): string
    {
        if ($relations === []) {
            return '';
        }

        return "\nuse Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;";
    }
}
