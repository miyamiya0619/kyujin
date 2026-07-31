<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JobPosting;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 企業・事業所紹介(求職者向け・公開ページ)。SPEC.md 5.1。
 */
class CompanyController extends Controller
{
    public function show(Company $company): View
    {
        // 掲載停止・契約終了の企業は公開ページに出さない。
        if (! $company->isActive()) {
            throw new NotFoundHttpException;
        }

        return view('public.companies.show', [
            'company' => $company->load('prefecture', 'city'),
            'workplaces' => $company->workplaces()->with('facilityType', 'prefecture', 'city')->orderBy('id')->get(),
            'jobPostings' => JobPosting::published()
                ->where('company_id', $company->id)
                ->with('workplace')
                ->orderByDesc('published_at')
                ->get(),
        ]);
    }
}
