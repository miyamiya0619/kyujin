<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyPlanAssignmentRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * 掲載企業への掲載プラン割当(運営者)。
 *
 * 新しいプランを割り当てると、現在有効な割当は自動的に終了させる。
 * 履歴として全件残す(SPEC.md 8.2 の company_plan_assignments)。
 */
class CompanyPlanAssignmentController extends Controller
{
    public function store(CompanyPlanAssignmentRequest $request, Company $company): RedirectResponse
    {
        $startsAt = $request->date('starts_at');

        DB::transaction(function () use ($request, $company, $startsAt) {
            $company->planAssignments()
                ->active()
                ->get()
                ->each(fn ($assignment) => $assignment->update([
                    'ends_at' => $startsAt->clone()->subDay(),
                ]));

            $company->planAssignments()->create([
                'posting_plan_id' => $request->integer('posting_plan_id'),
                'starts_at' => $startsAt,
                'ends_at' => null,
            ]);
        });

        return redirect()
            ->route('admin.companies.show', $company)
            ->with('status', '掲載プランを割り当てました。');
    }
}
