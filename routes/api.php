<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\IndexController;
use Illuminate\Support\Facades\Route;

Route::get('/', [IndexController::class, 'index']);

Route::prefix('user')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:cms-login');
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:cms-login');
    Route::post('resetpassword', [AuthController::class, 'resetPassword'])->middleware('throttle:cms-login');
    Route::middleware('api.member')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::post('changepassword', [AuthController::class, 'changePassword']);
    });
});
