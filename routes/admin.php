<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\PasswordResetController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\CompanyUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\WorkplaceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 運営者(メディア運営者)の管理画面
|--------------------------------------------------------------------------
| bootstrap/app.php から prefix「admin」・name「admin.」付きで読み込まれる。
| 認証は必ず auth:admin を明示すること。無指定だと既定ガード(seeker)になり穴が開く。
*/

Route::middleware('guest:admin')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');

    Route::get('forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'email'])->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::middleware('auth:admin')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    // 掲載企業の管理。運営者は全ての掲載企業を扱える。
    // 削除は提供しない。求人と応募が紐づくため、ステータスを archived にして止める。
    Route::resource('companies', CompanyController::class)->except(['destroy']);

    // 担当者アカウントの発行。掲載企業側からは追加できない(SPEC.md 5.3)。
    Route::post('companies/{company}/users', [CompanyUserController::class, 'store'])
        ->name('companies.users.store');
    Route::post('companies/{company}/users/{user}/resend', [CompanyUserController::class, 'resend'])
        ->name('companies.users.resend');
    Route::post('companies/{company}/users/{user}/toggle', [CompanyUserController::class, 'toggle'])
        ->name('companies.users.toggle');

    // 事業所の代行編集。運営者は立ち上げ期に掲載企業に代わって登録する(SPEC.md 11.6)。
    // shallow にしない: edit/update/destroy でも {company} を残し、
    // ManagesWorkplaces トレイトが常に対象企業を受け取れるようにする。
    Route::resource('companies.workplaces', WorkplaceController::class)->except(['show']);
});
