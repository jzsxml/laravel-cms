<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'code' => 1,
        'msg' => 'Demo addon admin route OK',
        'data' => ['addon' => 'demo', 'scope' => 'admin'],
    ]);
});