<?php

use App\Http\Controllers\Company\ApplicationController;
use App\Http\Controllers\Company\Auth\LoginController;
use App\Http\Controllers\Company\Auth\PasswordResetController;
use App\Http\Controllers\Company\DashboardController;
use App\Http\Controllers\Company\JobPostingController;
use App\Http\Controllers\Company\ProfileController;
use App\Http\Controllers\Company\WorkplaceController;
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

    // 自社の企業情報。**URL に企業 ID を含めない。**
    // 対象は常にログイン中の担当者が所属する企業であり、
    // ID を受け取らないことで他社を覗く余地を構造的に無くしている。
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    // 事業所の管理。**URL に企業 ID を含めない**(CLAUDE.md 3.8)。
    Route::resource('workplaces', WorkplaceController::class)->except(['show']);

    // 求人のセルフ入稿。**URL に企業 ID を含めない**(CLAUDE.md 3.8)。
    Route::resource('job-postings', JobPostingController::class)->except(['show']);
    Route::post('job-postings/{job_posting}/duplicate', [JobPostingController::class, 'duplicate'])
        ->name('job-postings.duplicate');
    Route::post('job-postings/{job_posting}/submit', [JobPostingController::class, 'submit'])
        ->name('job-postings.submit');

    // 応募者管理。**URL に企業 ID を含めない**(CLAUDE.md 3.8)。
    // export は {application} より先に登録し、"export" を応募 ID と誤認識させない。
    Route::get('applications/export', [ApplicationController::class, 'exportCsv'])->name('applications.export');
    Route::get('applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::put('applications/{application}/status', [ApplicationController::class, 'updateStatus'])
        ->name('applications.status.update');
    Route::post('applications/{application}/notes', [ApplicationController::class, 'storeNote'])
        ->name('applications.notes.store');
    Route::delete('applications/{application}/notes/{note}', [ApplicationController::class, 'destroyNote'])
        ->name('applications.notes.destroy');
});
