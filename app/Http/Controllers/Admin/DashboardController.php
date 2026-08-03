<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobPosting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

/**
 * 運営者のダッシュボード(SPEC.md 5.3)。
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $applicationCounts = Application::query()
            ->select('referrer_source', DB::raw('count(*) as count'))
            ->groupBy('referrer_source')
            ->pluck('count', 'referrer_source');

        $maxApplicationCount = max(1, $applicationCounts->max() ?? 0);

        return view('admin.dashboard', [
            'pendingCount' => JobPosting::where('status', JobPosting::STATUS_PENDING)->count(),
            'publishedCount' => JobPosting::published()->count(),
            'monthlyApplicationsCount' => Application::whereBetween('applied_at', [
                now()->startOfMonth(), now()->endOfMonth(),
            ])->count(),
            'pendingReviews' => JobPosting::where('status', JobPosting::STATUS_PENDING)
                ->with('company', 'workplace')
                ->orderBy('updated_at')
                ->limit(3)
                ->get(),
            'mediaNames' => FeedController::MEDIA_NAMES,
            'applicationCounts' => $applicationCounts,
            'maxApplicationCount' => $maxApplicationCount,
        ]);
    }
}
