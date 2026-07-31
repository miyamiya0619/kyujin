<?php

namespace App\Http\Controllers\Seeker;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * 求職者のマイページ。応募履歴は T-12 で実装する。
 */
class MyPageController extends Controller
{
    public function __invoke(): View
    {
        return view('seeker.mypage');
    }
}
