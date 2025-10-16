<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/**
 * 관리자 Auth-Emoney 라우트
 * 미들웨어: admin (관리자 전용)
 */

// 은행 관리 (AuthBank)
Route::prefix('auth/bank')->middleware(['admin'])->name('admin.auth.bank.')->group(function () {
    Route::get('/', \Jiny\AuthEmoney\Http\Controllers\Admin\AuthBank\IndexController::class)->name('index');
    Route::get('/export', \Jiny\AuthEmoney\Http\Controllers\Admin\AuthBank\ExportController::class)->name('export');
    Route::get('/create', \Jiny\AuthEmoney\Http\Controllers\Admin\AuthBank\CreateController::class)->name('create');
    Route::post('/', \Jiny\AuthEmoney\Http\Controllers\Admin\AuthBank\StoreController::class)->name('store');
    Route::get('/{id}', \Jiny\AuthEmoney\Http\Controllers\Admin\AuthBank\ShowController::class)->name('show');
    Route::get('/{id}/edit', \Jiny\AuthEmoney\Http\Controllers\Admin\AuthBank\EditController::class)->name('edit');
    Route::put('/{id}', \Jiny\AuthEmoney\Http\Controllers\Admin\AuthBank\UpdateController::class)->name('update');
    Route::delete('/{id}', \Jiny\AuthEmoney\Http\Controllers\Admin\AuthBank\DestroyController::class)->name('destroy');
});

// 이머니 관리 (Emoney)
Route::prefix('auth/emoney')->middleware(['admin'])->name('admin.auth.emoney.')->group(function () {
    Route::get('/', \Jiny\AuthEmoney\Http\Controllers\Admin\Emoney\IndexController::class)->name('index');
    Route::get('/create', \Jiny\AuthEmoney\Http\Controllers\Admin\Emoney\CreateController::class)->name('create');
    Route::post('/', \Jiny\AuthEmoney\Http\Controllers\Admin\Emoney\StoreController::class)->name('store');
    Route::get('/deposits', \Jiny\AuthEmoney\Http\Controllers\Admin\Emoney\DepositsController::class)->name('deposits');
    Route::get('/withdrawals', \Jiny\AuthEmoney\Http\Controllers\Admin\Emoney\WithdrawalsController::class)->name('withdrawals');

    // Bank 관리 라우트를 여기로 이동 (더 구체적인 라우트가 먼저 와야 함)
    Route::prefix('bank')->name('bank.')->group(function () {
        Route::get('/', \Jiny\AuthEmoney\Http\Controllers\Emoney\Bank\IndexController::class)->name('index');
        Route::get('/create', \Jiny\AuthEmoney\Http\Controllers\Emoney\Bank\CreateController::class)->name('create');
        Route::post('/', \Jiny\AuthEmoney\Http\Controllers\Emoney\Bank\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\AuthEmoney\Http\Controllers\Emoney\Bank\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\AuthEmoney\Http\Controllers\Emoney\Bank\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\AuthEmoney\Http\Controllers\Emoney\Bank\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\AuthEmoney\Http\Controllers\Emoney\Bank\DestroyController::class)->name('destroy');
    });

    // 다른 이머니 관련 라우트들
    Route::get('/deposit', \Jiny\AuthEmoney\Http\Controllers\Emoney\DepositController::class)->name('deposit');
    Route::get('/withdraw', \Jiny\AuthEmoney\Http\Controllers\Emoney\WithdrawController::class)->name('withdraw');
    Route::get('/log', \Jiny\AuthEmoney\Http\Controllers\Emoney\LogController::class)->name('log');

    Route::get('/{id}', \Jiny\AuthEmoney\Http\Controllers\Admin\Emoney\ShowController::class)->name('show');
    Route::get('/{id}/edit', \Jiny\AuthEmoney\Http\Controllers\Admin\Emoney\EditController::class)->name('edit');
    Route::put('/{id}', \Jiny\AuthEmoney\Http\Controllers\Admin\Emoney\UpdateController::class)->name('update');
    Route::delete('/{id}', \Jiny\AuthEmoney\Http\Controllers\Admin\Emoney\DeleteController::class)->name('destroy');
});

// Auth-Emoney 포인트 관리 (Point Management)
Route::prefix('auth/point')->middleware(['admin'])->name('admin.auth.point.')->group(function () {
    Route::get('/', \Jiny\AuthEmoney\Http\Controllers\Point\IndexController::class)->name('index');
    Route::get('/log', \Jiny\AuthEmoney\Http\Controllers\Point\LogController::class)->name('log');
    Route::get('/expiry', \Jiny\AuthEmoney\Http\Controllers\Point\ExpiryController::class)->name('expiry');
    Route::get('/stats', \Jiny\AuthEmoney\Http\Controllers\Point\StatsController::class)->name('stats');
});
