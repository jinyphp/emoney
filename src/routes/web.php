<?php

use Illuminate\Support\Facades\Route;
use Jiny\Emoney\Http\Controllers\Emoney\IndexController;
use Jiny\Emoney\Http\Controllers\Emoney\DepositController;
use Jiny\Emoney\Http\Controllers\Emoney\WithdrawController;
use Jiny\Emoney\Http\Controllers\Emoney\LogController;

// Front-end Emoney Routes (for users)
Route::middleware(['web', 'auth'])->prefix('emoney')->group(function () {
    Route::get('/', [IndexController::class, 'index'])->name('emoney.index');
    Route::get('/deposit', [DepositController::class, 'index'])->name('emoney.deposit');
    Route::post('/deposit', [DepositController::class, 'store'])->name('emoney.deposit.store');
    Route::get('/withdraw', [WithdrawController::class, 'index'])->name('emoney.withdraw');
    Route::post('/withdraw', [WithdrawController::class, 'store'])->name('emoney.withdraw.store');
    Route::get('/log', [LogController::class, 'index'])->name('emoney.log');
});