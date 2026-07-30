<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * 公開メディアのトップページ。
 *
 * T-10 で注目求人・新着求人・条件検索を実装する。
 * 表示に必要なデータは全てここで用意してビューに渡すこと。
 * テーマ側でデータを取りに行かせてはいけない(CLAUDE.md 3.2)。
 */
class TopController extends Controller
{
    public function __invoke(): View
    {
        return view('public.top');
    }
}
