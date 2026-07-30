<?php

use App\Http\Controllers\Public\TopController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 公開メディアサイト(求職者が見る画面)
|--------------------------------------------------------------------------
| SEO が生命線のため Blade によるサーバサイドレンダリングにする。
| ビューは「テーマ → コア」の順に解決される(ThemeManager)。
*/

Route::get('/', TopController::class)->name('public.top');
