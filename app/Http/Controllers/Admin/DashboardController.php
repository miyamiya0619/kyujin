<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobPosting;
use Illuminate\Contracts\View\View;

/**
 * 運営者のダッシュボード(SPEC.md 5.3)。
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'pendingCount' => JobPosting::where('status', JobPosting::STATUS_PENDING)->count(),
            'publishedCount' => JobPosting::published()->count(),
            'monthlyApplicationsCount' => Application::whereBetween('applied_at', [
                now()->startOfMonth(), now()->endOfMonth(),
            ])->count(),
        ]);
    }
}
