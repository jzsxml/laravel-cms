<?php

use Illuminate\Support\Facades\Route;

/** @var array<string, class-string> $generated */
$generated = require dirname(__DIR__).'/bootstrap/cms/generated_modules.php';

foreach ($generated as $slug => $controller) {
    Route::get($slug.'/export', [$controller, 'export']);
    Route::get($slug, [$controller, 'index']);
    Route::get($slug.'/add', [$controller, 'add']);
    Route::post($slug.'/add', [$controller, 'add']);
    Route::get($slug.'/edit', [$controller, 'edit']);
    Route::post($slug.'/edit', [$controller, 'edit']);
    Route::post($slug.'/del', [$controller, 'del']);
    Route::post($slug.'/multi', [$controller, 'multi']);
    Route::get($slug.'/recyclebin', [$controller, 'recyclebin']);
    Route::post($slug.'/restore', [$controller, 'restore']);
    Route::post($slug.'/destroy', [$controller, 'destroy']);
}
