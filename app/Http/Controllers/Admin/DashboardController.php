<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use Illuminate\Contracts\View\View;

/**
 * 運営者のダッシュボード。
 * T-16 で掲載中求人数・応募数・媒体別流入を追加する。
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'pendingCount' => JobPosting::where('status', JobPosting::STATUS_PENDING)->count(),
        ]);
    }
}
