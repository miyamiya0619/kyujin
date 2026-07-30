<?php

use App\Http\Controllers\Public\TopController;
use App\Http\Controllers\Seeker\Auth\LoginController;
use App\Http\Controllers\Seeker\Auth\PasswordResetController;
use App\Http\Controllers\Seeker\MyPageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 公開メディアサイト(求職者が見る画面)
|--------------------------------------------------------------------------
| SEO が生命線のため Blade によるサーバサイドレンダリングにする。
| ビューは「テーマ → コア」の順に解決される(ThemeManager)。
|
| 運営者と掲載企業の管理画面は routes/admin.php / routes/company.php にある。
*/

Route::get('/', TopController::class)->name('public.top');

/*
|--------------------------------------------------------------------------
| 求職者
|--------------------------------------------------------------------------
| 公開サイトの中に置く。管理画面と違って URL に接頭辞を付けない。
*/

Route::name('seeker.')->group(function () {
    Route::middleware('guest:seeker')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');

        Route::get('forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
        Route::post('forgot-password', [PasswordResetController::class, 'email'])->name('password.email');
        Route::get('reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
        Route::post('reset-password', [PasswordResetController::class, 'update'])->name('password.update');
    });

    Route::middleware('auth:seeker')->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::get('mypage', MyPageController::class)->name('mypage');
    });
});
