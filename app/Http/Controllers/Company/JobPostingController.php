<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Concerns\ManagesJobPostings;
use App\Http\Controllers\Controller;
use App\Http\Requests\JobPostingRequest;
use App\Models\Company;
use App\Models\JobPosting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * 求人の管理(掲載企業のセルフ入稿)。
 *
 * **URL に企業 ID を含めない**(CLAUDE.md 3.8)。対象は常にログイン中の担当者の所属企業。
 */
class JobPostingController extends Controller
{
    use ManagesJobPostings;

    public function index(): View
    {
        return $this->doIndex(null);
    }

    public function create(): View
    {
        return $this->doCreate(null);
    }

    public function store(JobPostingRequest $request): RedirectResponse
    {
        return $this->doStore($request, null);
    }

    public function edit(JobPosting $jobPosting): View
    {
        return $this->doEdit(null, $jobPosting);
    }

    public function update(JobPostingRequest $request, JobPosting $jobPosting): RedirectResponse
    {
        return $this->doUpdate($request, null, $jobPosting);
    }

    public function destroy(JobPosting $jobPosting): RedirectResponse
    {
        return $this->doDestroy(null, $jobPosting);
    }

    public function duplicate(JobPosting $jobPosting): RedirectResponse
    {
        return $this->doDuplicate(null, $jobPosting);
    }

    protected function targetCompany(?Company $routeCompany = null): Company
    {
        return auth('company')->user()->company;
    }

    protected function viewPrefix(): string
    {
        return 'company.job-postings';
    }

    protected function redirectRoute(Company $company): string
    {
        return route('company.job-postings.index');
    }
}
