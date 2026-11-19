<?php

use App\Http\Controllers\Portal\CommentController;
use App\Http\Controllers\Portal\DownloadController;
use App\Http\Controllers\Portal\LikeController;
use App\Http\Controllers\Portal\MemberHomeController;
use App\Http\Controllers\Portal\ModelDataController as PortalModelDataController;
use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\Portal\PurchaseController;
use App\Http\Middleware\PortalSecurityHeaders;
use App\Http\Middleware\SharePortalTheme;
use Illuminate\Support\Facades\Route;

Route::middleware([SharePortalTheme::class, PortalSecurityHeaders::class])->group(function () {
    Route::get('/', [PortalController::class, 'home'])->name('portal.home');
    Route::get('/s/{slug}', [PortalController::class, 'special'])->name('portal.special');
    Route::get('/u/{username}', [MemberHomeController::class, 'show'])->name('portal.member');
    Route::get('/m/{name}', [PortalModelDataController::class, 'index'])->name('portal.model');
    Route::post('/c/{nickname}/{slug}/comment', [CommentController::class, 'store'])->name('portal.comment');
    Route::post('/c/{nickname}/{slug}/purchase', [PurchaseController::class, 'archive'])->name('portal.purchase.archive');
    Route::get('/c/{nickname}/{slug}/download', [DownloadController::class, 'archive'])->name('portal.download.archive');
    Route::post('/c/{nickname}/{slug}/like', [LikeController::class, 'archive'])->name('portal.like.archive');
    Route::post('/p/{slug}/purchase', [PurchaseController::class, 'page'])->name('portal.purchase.page');
    Route::get('/p/{slug}/download', [DownloadController::class, 'page'])->name('portal.download.page');
    Route::get('/search', [PortalController::class, 'search'])->name('portal.search');
    Route::get('/tag/{slug}', [PortalController::class, 'tag'])->name('portal.tag');
    Route::get('/p/{slug}', [PortalController::class, 'page'])->name('portal.page');
    Route::get('/c/{nickname}', [PortalController::class, 'category'])->name('portal.category');
    Route::get('/c/{nickname}/{slug}', [PortalController::class, 'archive'])->name('portal.archive');
});
