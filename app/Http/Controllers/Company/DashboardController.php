<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Services\PostingPlanLimitService;
use Illuminate\Contracts\View\View;

/**
 * 掲載企業のダッシュボード。
 * T-13 で未対応の応募件数を追加する。
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
        ]);
    }
}
