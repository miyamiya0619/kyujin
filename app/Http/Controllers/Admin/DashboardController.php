<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * 運営者のダッシュボード。
 * T-16 で審査待ち件数・掲載中求人数・応募数・媒体別流入を表示する。
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard');
    }
}
