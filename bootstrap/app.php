<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // 管理画面は文脈ごとにルートファイルを分ける。
            // 画面数が多くなるため、1 ファイルにまとめると見通しが悪くなる。
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->prefix('company')
                ->name('company.')
                ->group(base_path('routes/company.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 未ログインでアクセスされたとき、その画面に対応するログイン画面へ送る。
        // 3 つのガードがあるため URL から文脈を判定する。
        $middleware->redirectGuestsTo(function (Request $request) {
            return match (true) {
                $request->is('admin', 'admin/*') => route('admin.login'),
                $request->is('company', 'company/*') => route('company.login'),
                default => route('seeker.login'),
            };
        });

        // ログイン済みでログイン画面にアクセスされたときの遷移先。
        $middleware->redirectUsersTo(function (Request $request) {
            return match (true) {
                $request->is('admin', 'admin/*') => route('admin.dashboard'),
                $request->is('company', 'company/*') => route('company.dashboard'),
                default => route('seeker.mypage'),
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
