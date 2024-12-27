<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/**
 * 회원 적립금 관리
 * 라우트 그룹
 */
Route::middleware(['web','auth:sanctum', 'verified'])
->name('user.')
->prefix('/home')->group(function () {
    // my적립급 목록
    Route::get('emoney',[
        \Jiny\Users\Emoney\Http\Controllers\Home\SiteUserEmoney::class,
        "index"]);

    // 회원 적립금 충전
    Route::get('emoney/deposit',[
        \Jiny\Users\Emoney\Http\Controllers\Home\SiteUserEmoneyDeposit::class,
        "index"]);

    // 회원 적립금 출금
    Route::get('emoney/withdraw',[
        \Jiny\Users\Emoney\Http\Controllers\Home\SiteUserEmoneyWithdraw::class,
        "index"]);

    // 회원 적립금 은행 목록
    Route::get('emoney/bank',[
        \Jiny\Users\Emoney\Http\Controllers\Home\SiteUserEmoneyBank::class,
        "index"]);
});



if(function_exists("isAdminPackage")) {

    // admin prefix 모듈 검사
    if(function_exists('admin_prefix')) {
        $prefix = admin_prefix();
    } else {
        $prefix = "admin";
    }


    ## 인증 Admin
    Route::middleware(['web','auth:sanctum', 'verified', 'admin'])
    ->name('admin.auth')
    ->prefix($prefix.'/auth')->group(function () {

        Route::get('emoney',[
            \Jiny\Users\Emoney\Http\Controllers\Admin\AdminEmoney::class,
            "index"]);

        // 회원 적립금 관리
        Route::get('emoney/user',[
            \Jiny\Users\Emoney\Http\Controllers\Admin\AdminUserEmoney::class,
            "index"]);

        // 회원 적립금 내역
        Route::get('emoney/log/{id?}',[
            \Jiny\Users\Emoney\Http\Controllers\Admin\AdminUserEmoneyLog::class,
            "index"])->where('id', '[0-9]+');

        Route::get('emoney/bank/{id?}',[
            \Jiny\Users\Emoney\Http\Controllers\Admin\AdminUserEmoneyBank::class,
            "index"])->where('id', '[0-9]+');

        // 회원 출금 내역
        Route::get('emoney/withdraw/{id?}',[
            \Jiny\Users\Emoney\Http\Controllers\Admin\AdminUserEmoneyWithdraw::class,
            "index"])->where('id', '[0-9]+');

        // 회원 입금 내역
        Route::get('emoney/deposit/{id?}',[
            \Jiny\Users\Emoney\Http\Controllers\Admin\AdminUserEmoneyDeposit::class,
            "index"])->where('id', '[0-9]+');

        Route::get('bank',[
            \Jiny\Users\Emoney\Http\Controllers\Admin\AdminAuthBank::class,
            "index"]);

        Route::get('currency',[
            \Jiny\Users\Emoney\Http\Controllers\Admin\AdminAuthCurrency::class,
            "index"]);

        Route::get('currency/log/{code?}',[
            \Jiny\Users\Emoney\Http\Controllers\Admin\AdminAuthCurrencyLog::class,
            "index"]);

    });
}
