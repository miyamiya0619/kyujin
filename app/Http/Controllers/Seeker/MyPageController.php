<?php

namespace App\Http\Controllers\Seeker;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * 求職者のマイページ。
 * T-11 でプロフィール・保有資格・職務経歴、T-12 で応募履歴を実装する。
 */
class MyPageController extends Controller
{
    public function __invoke(): View
    {
        return view('seeker.mypage');
    }
}
