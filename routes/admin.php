<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/**
 * 관리자 Emoney 라우트
 * 미들웨어: web, admin (웹 세션 + 관리자 전용)
 */

// Admin Auth 프리픽스 및 미들웨어 그룹
Route::prefix('admin/auth')->middleware(['web', 'admin'])->name('admin.auth.')->group(function () {

    // 은행 관리 (AuthBank)
    Route::prefix('bank')->name('bank.')->group(function () {
        Route::get('/', \Jiny\Emoney\Http\Controllers\Admin\AuthBank\IndexController::class)->name('index');
        Route::get('/export', \Jiny\Emoney\Http\Controllers\Admin\AuthBank\ExportController::class)->name('export');
        Route::get('/create', \Jiny\Emoney\Http\Controllers\Admin\AuthBank\CreateController::class)->name('create');
        Route::post('/', \Jiny\Emoney\Http\Controllers\Admin\AuthBank\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Emoney\Http\Controllers\Admin\AuthBank\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Emoney\Http\Controllers\Admin\AuthBank\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Emoney\Http\Controllers\Admin\AuthBank\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Emoney\Http\Controllers\Admin\AuthBank\DestroyController::class)->name('destroy');
    });

    // 이머니 관리 (Emoney)
    Route::prefix('emoney')->name('emoney.')->group(function () {
        // 기본 CRUD
        Route::get('/', \Jiny\Emoney\Http\Controllers\Admin\Emoney\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Emoney\Http\Controllers\Admin\Emoney\CreateController::class)->name('create');
        Route::post('/', \Jiny\Emoney\Http\Controllers\Admin\Emoney\StoreController::class)->name('store');

        // 시스템 설정
        Route::prefix('setting')->name('setting.')->group(function () {
            Route::get('/', [\Jiny\Emoney\Http\Controllers\Admin\Setting\IndexController::class, 'index'])->name('index');
            Route::post('/', [\Jiny\Emoney\Http\Controllers\Admin\Setting\IndexController::class, 'store'])->name('store');
            Route::post('/reset', [\Jiny\Emoney\Http\Controllers\Admin\Setting\IndexController::class, 'reset'])->name('reset');
            Route::get('/backup', [\Jiny\Emoney\Http\Controllers\Admin\Setting\IndexController::class, 'backup'])->name('backup');
        });

        // 충전 관리
        Route::prefix('deposits')->name('deposits.')->group(function () {
            Route::get('/', \Jiny\Emoney\Http\Controllers\Admin\Deposit\IndexController::class)->name('index');
            Route::get('/{id}', \Jiny\Emoney\Http\Controllers\Admin\Deposit\ShowController::class)->name('show');
            Route::post('/{id}/approve', \Jiny\Emoney\Http\Controllers\Admin\Deposit\ApproveController::class)->name('approve');
            Route::post('/{id}/reject', \Jiny\Emoney\Http\Controllers\Admin\Deposit\RejectController::class)->name('reject');
            Route::delete('/{id}', \Jiny\Emoney\Http\Controllers\Admin\Deposit\DeleteController::class)->name('delete');
        });

        // 출금 관리
        Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
            Route::get('/', \Jiny\Emoney\Http\Controllers\Admin\Withdrawal\IndexController::class)->name('index');
            Route::post('/{id}/approve', \Jiny\Emoney\Http\Controllers\Admin\Withdrawal\ApproveController::class)->name('approve');
            Route::post('/{id}/reject', \Jiny\Emoney\Http\Controllers\Admin\Withdrawal\RejectController::class)->name('reject');
        });

        // 이머니 계정 관리 (동적 라우트는 마지막에)
        Route::get('/{id}', \Jiny\Emoney\Http\Controllers\Admin\Emoney\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Emoney\Http\Controllers\Admin\Emoney\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Emoney\Http\Controllers\Admin\Emoney\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Emoney\Http\Controllers\Admin\Emoney\DeleteController::class)->name('destroy');
    });

    // 포인트 관리 (Point Management)
    Route::prefix('point')->name('point.')->group(function () {
        // 포인트 로그 관리
        Route::get('/log', \Jiny\Emoney\Http\Controllers\Admin\Point\LogController::class)->name('log');

        // 포인트 만료 관리
        Route::prefix('expiry')->name('expiry.')->group(function () {
            Route::get('/', \Jiny\Emoney\Http\Controllers\Admin\Point\ExpiryController::class)->name('index');
            Route::get('/export', [\Jiny\Emoney\Http\Controllers\Admin\Point\ExpiryController::class, 'export'])->name('export');
            Route::get('/{id}', [\Jiny\Emoney\Http\Controllers\Admin\Point\ExpiryController::class, 'show'])->name('show');
            Route::post('/{id}/process', [\Jiny\Emoney\Http\Controllers\Admin\Point\ExpiryController::class, 'processExpiry'])->name('process');
            Route::post('/{id}/notify', [\Jiny\Emoney\Http\Controllers\Admin\Point\ExpiryController::class, 'sendNotification'])->name('notify');
            Route::delete('/{id}', [\Jiny\Emoney\Http\Controllers\Admin\Point\ExpiryController::class, 'destroy'])->name('destroy');
        });

        // 포인트 통계
        Route::get('/stats', \Jiny\Emoney\Http\Controllers\Admin\Point\StatsController::class)->name('stats');

        // AJAX API 라우트
        Route::post('/search-member', \Jiny\Emoney\Http\Controllers\Admin\Point\SearchMemberController::class)->name('search-member');
        Route::post('/search-member-sharded', \Jiny\Emoney\Http\Controllers\Admin\Point\SearchMemberShardedController::class)->name('search-member-sharded');
        Route::post('/adjust', \Jiny\Emoney\Http\Controllers\Admin\Point\AdjustController::class)->name('adjust');
        Route::get('/recent-adjustments/{member_id}', \Jiny\Emoney\Http\Controllers\Admin\Point\RecentAdjustmentsController::class)->name('recent-adjustments');

        // 포인트 CRUD (동적 라우트는 마지막에)
        Route::get('/', \Jiny\Emoney\Http\Controllers\Admin\Point\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Emoney\Http\Controllers\Admin\Point\CreateController::class)->name('create');
        Route::post('/', \Jiny\Emoney\Http\Controllers\Admin\Point\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Emoney\Http\Controllers\Admin\Point\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Emoney\Http\Controllers\Admin\Point\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Emoney\Http\Controllers\Admin\Point\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Emoney\Http\Controllers\Admin\Point\DestroyController::class)->name('destroy');
    });
});
