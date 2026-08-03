<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobPosting;
use App\Services\PostingPlanLimitService;
use Illuminate\Contracts\View\View;

/**
 * 掲載企業のダッシュボード。
 */
class DashboardController extends Controller
{
    public function __invoke(PostingPlanLimitService $limits): View
    {
        $company = auth('company')->user()->company;

        return view('company.dashboard', [
            'currentPlan' => $limits->currentPlan($company),
            'remainingJobPostingSlots' => $limits->remainingJobPostingSlots($company),
            'remainingWorkplaceSlots' => $limits->remainingWorkplaceSlots($company),
            'publishedJobPostingCount' => $company->jobPostings()->where('status', JobPosting::STATUS_PUBLISHED)->count(),
            'newApplicationsCount' => Application::where('company_id', $company->id)
                ->where('status', Application::STATUS_NEW)
                ->count(),
            'recentApplications' => Application::where('company_id', $company->id)
                ->with('jobPosting')
                ->latest('applied_at')
                ->limit(5)
                ->get(),
        ]);
    }
}
