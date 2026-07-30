<?php

use App\Http\Controllers\Company\Auth\LoginController;
use App\Http\Controllers\Company\Auth\PasswordResetController;
use App\Http\Controllers\Company\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 掲載企業の管理画面
|--------------------------------------------------------------------------
| bootstrap/app.php から prefix「company」・name「company.」付きで読み込まれる。
| 認証は必ず auth:company を明示すること。
*/

Route::middleware('guest:company')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');

    Route::get('forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'email'])->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::middleware('auth:company')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');
});
