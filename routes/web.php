<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/**
 * 사용자 Auth-Emoney 라우트
 * 미들웨어: auth (로그인 사용자 전용)
 */

// 사용자 E-money 관리
Route::middleware(['auth'])->prefix('emoney')->name('emoney.')->group(function () {
    Route::get('/', \Jiny\AuthEmoney\Http\Controllers\Emoney\IndexController::class)->name('index');
    Route::get('/deposit', \Jiny\AuthEmoney\Http\Controllers\Emoney\DepositController::class)->name('deposit');
    Route::get('/withdraw', \Jiny\AuthEmoney\Http\Controllers\Emoney\WithdrawController::class)->name('withdraw');
    Route::get('/log', \Jiny\AuthEmoney\Http\Controllers\Emoney\LogController::class)->name('log');

    // 은행 계좌 관리
    Route::prefix('bank')->name('bank.')->group(function () {
        Route::get('/', \Jiny\AuthEmoney\Http\Controllers\Emoney\Bank\IndexController::class)->name('index');
        Route::get('/create', \Jiny\AuthEmoney\Http\Controllers\Emoney\Bank\CreateController::class)->name('create');
        Route::post('/', \Jiny\AuthEmoney\Http\Controllers\Emoney\Bank\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\AuthEmoney\Http\Controllers\Emoney\Bank\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\AuthEmoney\Http\Controllers\Emoney\Bank\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\AuthEmoney\Http\Controllers\Emoney\Bank\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\AuthEmoney\Http\Controllers\Emoney\Bank\DestroyController::class)->name('destroy');
    });
});
