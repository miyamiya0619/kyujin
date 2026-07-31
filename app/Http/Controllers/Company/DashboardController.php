<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Application;
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
            'newApplicationsCount' => Application::where('company_id', $company->id)
                ->where('status', Application::STATUS_NEW)
                ->count(),
        ]);
    }
}
