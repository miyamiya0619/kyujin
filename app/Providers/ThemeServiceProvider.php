<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\JobPosting;
use App\Models\SiteSetting;
use App\Support\Theme\ThemeManager;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class ThemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ThemeManager::class);
    }

    public function boot(): void
    {
        // ビューの探索順を「テーマ → コア」にする。
        // ビューが解決される前に済ませる必要があるため、起動時に行う。
        $this->app->make(ThemeManager::class)->applyViewPaths();

        $this->shareSiteSettings();
        $this->shareManageNavCounts();
    }

    /**
     * 全ビューでサイト設定を $site として参照できるようにする。
     *
     * **テーマがモデルを直接触らずに済むようにするための仕組み。**
     * テーマは `{{ $site->site_name }}` と書くだけでよく、
     * クエリを書く必要がない(CLAUDE.md 3.2)。
     */
    private function shareSiteSettings(): void
    {
        View::composer('*', function ($view) {
            try {
                $view->with('site', SiteSetting::current());
            } catch (Throwable) {
                // マイグレーション前など、設定を読めない状況でも
                // ビューの描画自体は失敗させない
                $view->with('site', new SiteSetting);
            }
        });
    }

    /**
     * 管理画面の左サイドナビに出す件数バッジ。
     *
     * `layouts.manage` はダッシュボード以外の全ページ(一覧・編集フォーム等)からも
     * 使われるため、個々のコントローラに件数取得を書かせず、ここで一元的に用意する
     * ($site と同じ考え方)。
     */
    private function shareManageNavCounts(): void
    {
        View::composer('layouts.manage', function ($view) {
            try {
                if (auth('admin')->check()) {
                    $view->with('pendingReviewCount', JobPosting::where('status', JobPosting::STATUS_PENDING)->count());
                } elseif (auth('company')->check()) {
                    $company = auth('company')->user()->company;
                    $view->with('unhandledApplicationCount', Application::where('company_id', $company->id)
                        ->where('status', Application::STATUS_NEW)
                        ->count());
                }
            } catch (Throwable) {
                // マイグレーション前などでテーブルが無くても管理画面の描画は失敗させない
            }
        });
    }
}
