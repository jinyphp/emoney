<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/**
 * 사용자 Emoney 라우트
 * 미들웨어: web, jwt.auth (웹 세션 + JWT 로그인 사용자 전용)
 */

// 사용자 E-money 관리
Route::middleware(['web', 'jwt'])->prefix('home/emoney')->name('home.emoney.')->group(function () {

    // 메인 이머니 대시보드 (기존 /home/dashboard/emoney를 대체)
    Route::get('/', \Jiny\Emoney\Http\Controllers\Emoney\Dashboard\IndexController::class)->name('index');

    // 충전 관리 (기존 라우트 이름 유지)
    Route::get('/deposit', \Jiny\Emoney\Http\Controllers\Emoney\Deposit\IndexController::class)->name('deposit');
    Route::post('/deposit', \Jiny\Emoney\Http\Controllers\Emoney\Deposit\StoreController::class)->name('deposit.store');
    Route::get('/deposit/history', \Jiny\Emoney\Http\Controllers\Emoney\Deposit\HistoryController::class)->name('deposit.history');
    Route::get('/deposit/{depositId}/status', \Jiny\Emoney\Http\Controllers\Emoney\Deposit\StatusController::class)->name('deposit.status');
    Route::post('/deposit/{depositId}/cancel', \Jiny\Emoney\Http\Controllers\Emoney\Deposit\CancelController::class)->name('deposit.cancel');

    // 출금 관리 (개선된 구조)
    Route::get('/withdraw', \Jiny\Emoney\Http\Controllers\Emoney\Withdraw\IndexController::class)->name('withdraw');
    Route::post('/withdraw', \Jiny\Emoney\Http\Controllers\Emoney\Withdraw\StoreController::class)->name('withdraw.store');
    Route::get('/withdraw/history', \Jiny\Emoney\Http\Controllers\Emoney\Withdraw\HistoryController::class)->name('withdraw.history');

    // 거래 내역
    Route::get('/log', \Jiny\Emoney\Http\Controllers\Emoney\Log\IndexController::class)->name('log');

    // 은행 계좌 관리
    Route::prefix('bank')->name('bank.')->group(function () {
        Route::get('/', \Jiny\Emoney\Http\Controllers\Bank\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Emoney\Http\Controllers\Bank\CreateController::class)->name('create');
        Route::post('/', \Jiny\Emoney\Http\Controllers\Bank\StoreController::class)->name('store');
        Route::get('/{id}/edit', \Jiny\Emoney\Http\Controllers\Bank\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Emoney\Http\Controllers\Bank\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Emoney\Http\Controllers\Bank\DeleteController::class)->name('delete');
        Route::post('/{id}/set-default', \Jiny\Emoney\Http\Controllers\Bank\SetDefaultController::class)->name('set-default');
    });

    // 포인트 관리
    Route::prefix('point')->name('point.')->group(function () {
        Route::get('/', \Jiny\Emoney\Http\Controllers\Point\Dashboard\IndexController::class)->name('index');
        Route::get('/log', \Jiny\Emoney\Http\Controllers\Point\Log\IndexController::class)->name('log');
        Route::get('/expiry', \Jiny\Emoney\Http\Controllers\Point\Expiry\IndexController::class)->name('expiry');
    });
});
