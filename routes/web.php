<?php

use App\Http\Controllers\Feed\FeedController;
use App\Http\Controllers\Public\ApplicationController;
use App\Http\Controllers\Public\CityController;
use App\Http\Controllers\Public\CompanyController;
use App\Http\Controllers\Public\JobPostingController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Public\TopController;
use App\Http\Controllers\Seeker\AccountController;
use App\Http\Controllers\Seeker\Auth\LoginController;
use App\Http\Controllers\Seeker\Auth\PasswordResetController;
use App\Http\Controllers\Seeker\Auth\RegisterController;
use App\Http\Controllers\Seeker\ExperienceController;
use App\Http\Controllers\Seeker\MyPageController;
use App\Http\Controllers\Seeker\ProfileController;
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

Route::name('public.')->group(function () {
    Route::get('jobs', [JobPostingController::class, 'index'])->name('jobs.index');
    Route::get('jobs/{job_posting}', [JobPostingController::class, 'show'])->name('jobs.show');
    Route::get('jobs/{job_posting}/apply', [ApplicationController::class, 'create'])->name('jobs.apply');
    Route::post('jobs/{job_posting}/apply', [ApplicationController::class, 'store'])->name('jobs.apply.store');
    Route::get('companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
    Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
});

// 都道府県セレクトの補助 API(x-prefecture-city-select-script から fetch する)。
// 認証不要。個人情報を含まない配布マスタの参照のみ。
Route::get('cities/by-prefecture/{prefecture}', [CityController::class, 'byPrefecture'])
    ->name('cities.by-prefecture');

// アグリゲーション媒体向け XML フィード(SPEC.md 10.1)。
Route::get('feed/indeed.xml', [FeedController::class, 'indeed'])->name('feed.indeed');
Route::get('feed/kyujinbox.xml', [FeedController::class, 'kyujinbox'])->name('feed.kyujinbox');
Route::get('feed/stanby.xml', [FeedController::class, 'stanby'])->name('feed.stanby');

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

        // 会員登録はサイト設定(enables_member)が OFF なら 404(SPEC.md 5.4)。
        Route::middleware('member.enabled')->group(function () {
            Route::get('register', [RegisterController::class, 'create'])->name('register');
            Route::post('register', [RegisterController::class, 'store'])->name('register.store');
        });

        Route::get('forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
        Route::post('forgot-password', [PasswordResetController::class, 'email'])->name('password.email');
        Route::get('reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
        Route::post('reset-password', [PasswordResetController::class, 'update'])->name('password.update');
    });

    Route::middleware('auth:seeker')->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::get('mypage', MyPageController::class)->name('mypage');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::post('experiences', [ExperienceController::class, 'store'])->name('experiences.store');
        Route::put('experiences/{experience}', [ExperienceController::class, 'update'])->name('experiences.update');
        Route::delete('experiences/{experience}', [ExperienceController::class, 'destroy'])->name('experiences.destroy');
        Route::post('experiences/reorder', [ExperienceController::class, 'reorder'])->name('experiences.reorder');

        Route::delete('account', [AccountController::class, 'destroy'])->name('account.destroy');
    });
});
