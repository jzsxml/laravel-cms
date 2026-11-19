<?php

use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json([
        'code' => 1,
        'msg' => 'pong',
        'data' => ['addon' => 'demo'],
    ]);
});