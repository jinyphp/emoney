<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/**
 * 관리자 Auth-Emoney 라우트
 * 미들웨어: web, admin (웹 세션 + 관리자 전용)
 */

// 은행 관리 (AuthBank)
Route::prefix('admin/auth/bank')->middleware(['web', 'admin'])->name('admin.auth.bank.')->group(function () {
    Route::get('/', \Jiny\Auth\Emoney\Http\Controllers\Admin\AuthBank\IndexController::class)->name('index');
    Route::get('/export', \Jiny\Auth\Emoney\Http\Controllers\Admin\AuthBank\ExportController::class)->name('export');
    Route::get('/create', \Jiny\Auth\Emoney\Http\Controllers\Admin\AuthBank\CreateController::class)->name('create');
    Route::post('/', \Jiny\Auth\Emoney\Http\Controllers\Admin\AuthBank\StoreController::class)->name('store');
    Route::get('/{id}', \Jiny\Auth\Emoney\Http\Controllers\Admin\AuthBank\ShowController::class)->name('show');
    Route::get('/{id}/edit', \Jiny\Auth\Emoney\Http\Controllers\Admin\AuthBank\EditController::class)->name('edit');
    Route::put('/{id}', \Jiny\Auth\Emoney\Http\Controllers\Admin\AuthBank\UpdateController::class)->name('update');
    Route::delete('/{id}', \Jiny\Auth\Emoney\Http\Controllers\Admin\AuthBank\DestroyController::class)->name('destroy');
});

// 이머니 관리 (Emoney)
Route::prefix('admin/auth/emoney')->middleware(['web', 'admin'])->name('admin.auth.emoney.')->group(function () {
    Route::get('/', \Jiny\Auth\Emoney\Http\Controllers\Admin\Emoney\IndexController::class)->name('index');
    Route::get('/create', \Jiny\Auth\Emoney\Http\Controllers\Admin\Emoney\CreateController::class)->name('create');
    Route::post('/', \Jiny\Auth\Emoney\Http\Controllers\Admin\Emoney\StoreController::class)->name('store');
    Route::get('/deposits', \Jiny\Auth\Emoney\Http\Controllers\Admin\Emoney\DepositsController::class)->name('deposits');
    Route::get('/withdrawals', \Jiny\Auth\Emoney\Http\Controllers\Admin\Emoney\WithdrawalsController::class)->name('withdrawals');

    // EmoneyBank 관리 라우트 (Admin으로 이동됨)
    Route::prefix('bank')->name('bank.')->group(function () {
        Route::get('/', \Jiny\Auth\Emoney\Http\Controllers\Admin\EmoneyBank\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Auth\Emoney\Http\Controllers\Admin\EmoneyBank\CreateController::class)->name('create');
        Route::post('/', \Jiny\Auth\Emoney\Http\Controllers\Admin\EmoneyBank\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Auth\Emoney\Http\Controllers\Admin\EmoneyBank\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Auth\Emoney\Http\Controllers\Admin\EmoneyBank\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Auth\Emoney\Http\Controllers\Admin\EmoneyBank\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Auth\Emoney\Http\Controllers\Admin\EmoneyBank\DestroyController::class)->name('destroy');
    });

    // 다른 이머니 관련 라우트들
    Route::get('/deposit', \Jiny\Auth\Emoney\Http\Controllers\Emoney\DepositController::class)->name('deposit');
    Route::get('/withdraw', \Jiny\Auth\Emoney\Http\Controllers\Emoney\WithdrawController::class)->name('withdraw');
    Route::get('/log', \Jiny\Auth\Emoney\Http\Controllers\Emoney\LogController::class)->name('log');

    Route::get('/{id}', \Jiny\Auth\Emoney\Http\Controllers\Admin\Emoney\ShowController::class)->name('show');
    Route::get('/{id}/edit', \Jiny\Auth\Emoney\Http\Controllers\Admin\Emoney\EditController::class)->name('edit');
    Route::put('/{id}', \Jiny\Auth\Emoney\Http\Controllers\Admin\Emoney\UpdateController::class)->name('update');
    Route::delete('/{id}', \Jiny\Auth\Emoney\Http\Controllers\Admin\Emoney\DeleteController::class)->name('destroy');
});

// Auth-Emoney 포인트 관리 (Point Management)
Route::prefix('admin/auth/point')->middleware(['web', 'admin'])->name('admin.auth.point.')->group(function () {
    // CRUD 라우트
    Route::get('/', \Jiny\Auth\Emoney\Http\Controllers\Admin\Point\IndexController::class)->name('index');
    Route::get('/create', \Jiny\Auth\Emoney\Http\Controllers\Admin\Point\CreateController::class)->name('create');
    Route::post('/', \Jiny\Auth\Emoney\Http\Controllers\Admin\Point\StoreController::class)->name('store');
    Route::get('/{id}', \Jiny\Auth\Emoney\Http\Controllers\Admin\Point\ShowController::class)->name('show');
    Route::get('/{id}/edit', \Jiny\Auth\Emoney\Http\Controllers\Admin\Point\EditController::class)->name('edit');
    Route::put('/{id}', \Jiny\Auth\Emoney\Http\Controllers\Admin\Point\UpdateController::class)->name('update');
    Route::delete('/{id}', \Jiny\Auth\Emoney\Http\Controllers\Admin\Point\DestroyController::class)->name('destroy');

    // 관리 기능 라우트
    Route::get('/log', \Jiny\Auth\Emoney\Http\Controllers\Admin\Point\LogController::class)->name('log');
    Route::get('/expiry', \Jiny\Auth\Emoney\Http\Controllers\Admin\Point\ExpiryController::class)->name('expiry');
    Route::get('/stats', \Jiny\Auth\Emoney\Http\Controllers\Admin\Point\StatsController::class)->name('stats');
});
