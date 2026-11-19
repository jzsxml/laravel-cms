<?php

use App\Http\Controllers\Member\AuthController;
use App\Http\Controllers\Member\ContributeController;
use App\Http\Controllers\Member\IndexController;
use App\Http\Controllers\Member\ProfileController;
use App\Http\Controllers\Member\WalletController;
use Illuminate\Support\Facades\Route;

$memberPath = config('cms.member_path', 'user');

Route::middleware('web')->prefix($memberPath)->name('member.')->group(function () {
    Route::get('login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:cms-login')->name('login.post');
    Route::get('register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:cms-login')->name('register.post');

    Route::middleware('member.auth')->group(function () {
        Route::get('/', [IndexController::class, 'index'])->name('home');
        Route::get('index', [IndexController::class, 'index']);
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::match(['get', 'post'], 'profile', [ProfileController::class, 'index'])->name('profile');
        Route::get('contribute', [ContributeController::class, 'index'])->name('contribute');
        Route::post('contribute', [ContributeController::class, 'store'])->name('contribute.store');
        Route::get('wallet', [WalletController::class, 'index'])->name('wallet');
        Route::post('wallet/recharge', [WalletController::class, 'recharge'])->name('wallet.recharge');
    });
});
