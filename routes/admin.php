<?php

use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\PasswordResetController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\CompanyPlanAssignmentController;
use App\Http\Controllers\Admin\CompanyUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\JobPostingController;
use App\Http\Controllers\Admin\PostingPlanController;
use App\Http\Controllers\Admin\ReviewController;
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

    // 掲載プランの定義と、掲載企業への割当(SPEC.md 6.1)。
    Route::resource('posting-plans', PostingPlanController::class)->except(['show', 'destroy']);
    Route::post('companies/{company}/plan-assignments', [CompanyPlanAssignmentController::class, 'store'])
        ->name('companies.plan-assignments.store');

    // 事業所の代行編集。運営者は立ち上げ期に掲載企業に代わって登録する(SPEC.md 11.6)。
    // shallow にしない: edit/update/destroy でも {company} を残し、
    // ManagesWorkplaces トレイトが常に対象企業を受け取れるようにする。
    Route::resource('companies.workplaces', WorkplaceController::class)->except(['show']);

    // 求人の代行入稿。shallow にしない理由は事業所と同じ(CLAUDE.md 3.9)。
    Route::resource('companies.job-postings', JobPostingController::class)->except(['show']);
    Route::post('companies/{company}/job-postings/{job_posting}/duplicate', [JobPostingController::class, 'duplicate'])
        ->name('companies.job-postings.duplicate');
    Route::post('companies/{company}/job-postings/{job_posting}/submit', [JobPostingController::class, 'submit'])
        ->name('companies.job-postings.submit');

    // 審査待ち一覧と審査アクション。運営者の日常業務の中心(SPEC.md 7章)。
    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::post('job-postings/{job_posting}/approve', [ReviewController::class, 'approve'])
        ->name('job-postings.approve');
    Route::post('job-postings/{job_posting}/reject', [ReviewController::class, 'reject'])
        ->name('job-postings.reject');

    // 応募の横断確認。閲覧のみ(SPEC.md 5.3)。
    Route::get('applications', [ApplicationController::class, 'index'])->name('applications.index');
});
