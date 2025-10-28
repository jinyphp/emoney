<?php

use Illuminate\Support\Facades\Route;
use Jiny\Emoney\Http\Controllers\Admin\Emoney\IndexController;
use Jiny\Emoney\Http\Controllers\Admin\Emoney\CreateController;
use Jiny\Emoney\Http\Controllers\Admin\Emoney\StoreController;
use Jiny\Emoney\Http\Controllers\Admin\Emoney\ShowController;
use Jiny\Emoney\Http\Controllers\Admin\Emoney\EditController;
use Jiny\Emoney\Http\Controllers\Admin\Emoney\UpdateController;
use Jiny\Emoney\Http\Controllers\Admin\Emoney\DeleteController;
use Jiny\Emoney\Http\Controllers\Admin\Emoney\DepositsController;
use Jiny\Emoney\Http\Controllers\Admin\Emoney\WithdrawalsController;

use Jiny\Emoney\Http\Controllers\Admin\Point\IndexController as PointIndexController;
use Jiny\Emoney\Http\Controllers\Admin\Point\CreateController as PointCreateController;
use Jiny\Emoney\Http\Controllers\Admin\Point\StoreController as PointStoreController;
use Jiny\Emoney\Http\Controllers\Admin\Point\ShowController as PointShowController;
use Jiny\Emoney\Http\Controllers\Admin\Point\EditController as PointEditController;
use Jiny\Emoney\Http\Controllers\Admin\Point\UpdateController as PointUpdateController;
use Jiny\Emoney\Http\Controllers\Admin\Point\DestroyController as PointDestroyController;
use Jiny\Emoney\Http\Controllers\Admin\Point\LogController as PointLogController;
use Jiny\Emoney\Http\Controllers\Admin\Point\StatsController as PointStatsController;
use Jiny\Emoney\Http\Controllers\Admin\Point\ExpiryController as PointExpiryController;

use Jiny\Emoney\Http\Controllers\Admin\AuthBank\IndexController as AuthBankIndexController;
use Jiny\Emoney\Http\Controllers\Admin\AuthBank\CreateController as AuthBankCreateController;
use Jiny\Emoney\Http\Controllers\Admin\AuthBank\StoreController as AuthBankStoreController;
use Jiny\Emoney\Http\Controllers\Admin\AuthBank\ShowController as AuthBankShowController;
use Jiny\Emoney\Http\Controllers\Admin\AuthBank\EditController as AuthBankEditController;
use Jiny\Emoney\Http\Controllers\Admin\AuthBank\UpdateController as AuthBankUpdateController;
use Jiny\Emoney\Http\Controllers\Admin\AuthBank\DestroyController as AuthBankDestroyController;
use Jiny\Emoney\Http\Controllers\Admin\AuthBank\ExportController as AuthBankExportController;

use Jiny\Emoney\Http\Controllers\Admin\EmoneyBank\IndexController as EmoneyBankIndexController;
use Jiny\Emoney\Http\Controllers\Admin\EmoneyBank\CreateController as EmoneyBankCreateController;
use Jiny\Emoney\Http\Controllers\Admin\EmoneyBank\StoreController as EmoneyBankStoreController;
use Jiny\Emoney\Http\Controllers\Admin\EmoneyBank\ShowController as EmoneyBankShowController;
use Jiny\Emoney\Http\Controllers\Admin\EmoneyBank\EditController as EmoneyBankEditController;
use Jiny\Emoney\Http\Controllers\Admin\EmoneyBank\UpdateController as EmoneyBankUpdateController;
use Jiny\Emoney\Http\Controllers\Admin\EmoneyBank\DestroyController as EmoneyBankDestroyController;

// Admin Emoney Management Routes
Route::middleware(['web', 'admin'])->prefix('admin/auth')->group(function () {

    // Emoney 관리
    Route::prefix('emoney')->group(function () {
        Route::get('/', [IndexController::class, 'index'])->name('admin.auth.emoney.index');
        Route::get('/create', [CreateController::class, 'create'])->name('admin.auth.emoney.create');
        Route::post('/', [StoreController::class, 'store'])->name('admin.auth.emoney.store');
        Route::get('/{id}', [ShowController::class, 'show'])->name('admin.auth.emoney.show');
        Route::get('/{id}/edit', [EditController::class, 'edit'])->name('admin.auth.emoney.edit');
        Route::put('/{id}', [UpdateController::class, 'update'])->name('admin.auth.emoney.update');
        Route::delete('/{id}', [DeleteController::class, 'destroy'])->name('admin.auth.emoney.destroy');

        // 입출금 내역
        Route::get('/deposits', [DepositsController::class, 'index'])->name('admin.auth.emoney.deposits.index');
        Route::get('/withdrawals', [WithdrawalsController::class, 'index'])->name('admin.auth.emoney.withdrawals.index');

        // 설정 관리
        Route::get('/setting', [\Jiny\Emoney\Http\Controllers\Admin\Setting\IndexController::class, 'index'])->name('admin.auth.emoney.setting.index');
        Route::post('/setting', [\Jiny\Emoney\Http\Controllers\Admin\Setting\IndexController::class, 'store'])->name('admin.auth.emoney.setting.store');
        Route::post('/setting/reset', [\Jiny\Emoney\Http\Controllers\Admin\Setting\IndexController::class, 'reset'])->name('admin.auth.emoney.setting.reset');
        Route::get('/setting/backup', [\Jiny\Emoney\Http\Controllers\Admin\Setting\IndexController::class, 'backup'])->name('admin.auth.emoney.setting.backup');
    });

    // Point 관리
    Route::prefix('point')->group(function () {
        Route::get('/', [PointIndexController::class, 'index'])->name('admin.auth.point.index');
        Route::get('/create', [PointCreateController::class, 'create'])->name('admin.auth.point.create');
        Route::post('/', [PointStoreController::class, 'store'])->name('admin.auth.point.store');
        Route::get('/{id}', [PointShowController::class, 'show'])->name('admin.auth.point.show');
        Route::get('/{id}/edit', [PointEditController::class, 'edit'])->name('admin.auth.point.edit');
        Route::put('/{id}', [PointUpdateController::class, 'update'])->name('admin.auth.point.update');
        Route::delete('/{id}', [PointDestroyController::class, 'destroy'])->name('admin.auth.point.destroy');

        // 포인트 관련 추가 기능
        Route::get('/log', [PointLogController::class, 'index'])->name('admin.auth.point.log');
        Route::get('/stats', [PointStatsController::class, 'index'])->name('admin.auth.point.stats');
        Route::get('/expiry', [PointExpiryController::class, 'index'])->name('admin.auth.point.expiry');
    });

    // 은행 관리
    Route::prefix('bank')->group(function () {
        // API 라우트를 먼저 정의 (라우트 충돌 방지)
        Route::get('/api/banks/{countryCode}', [AuthBankCreateController::class, 'getBanksByCountry'])->name('admin.auth.bank.api.banks');
        Route::get('/api/edit/banks/{countryCode}', [AuthBankEditController::class, 'getBanksByCountry'])->name('admin.auth.bank.api.edit.banks');

        Route::get('/', [AuthBankIndexController::class, 'index'])->name('admin.auth.bank.index');
        Route::get('/create', [AuthBankCreateController::class, 'create'])->name('admin.auth.bank.create');
        Route::post('/', [AuthBankStoreController::class, 'store'])->name('admin.auth.bank.store');
        Route::get('/export', [AuthBankExportController::class, 'export'])->name('admin.auth.bank.export');
        Route::get('/{id}', [AuthBankShowController::class, 'show'])->name('admin.auth.bank.show');
        Route::get('/{id}/edit', [AuthBankEditController::class, 'edit'])->name('admin.auth.bank.edit');
        Route::put('/{id}', [AuthBankUpdateController::class, 'update'])->name('admin.auth.bank.update');
        Route::delete('/{id}', [AuthBankDestroyController::class, 'destroy'])->name('admin.auth.bank.destroy');
    });

    // 사용자 은행계좌 관리
    Route::prefix('emoney/bank')->group(function () {
        Route::get('/', [EmoneyBankIndexController::class, 'index'])->name('admin.auth.emoney.bank.index');
        Route::get('/create', [EmoneyBankCreateController::class, 'create'])->name('admin.auth.emoney.bank.create');
        Route::post('/', [EmoneyBankStoreController::class, 'store'])->name('admin.auth.emoney.bank.store');
        Route::get('/{id}', [EmoneyBankShowController::class, 'show'])->name('admin.auth.emoney.bank.show');
        Route::get('/{id}/edit', [EmoneyBankEditController::class, 'edit'])->name('admin.auth.emoney.bank.edit');
        Route::put('/{id}', [EmoneyBankUpdateController::class, 'update'])->name('admin.auth.emoney.bank.update');
        Route::delete('/{id}', [EmoneyBankDestroyController::class, 'destroy'])->name('admin.auth.emoney.bank.destroy');
    });
});