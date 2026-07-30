<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManagesJobPostings;
use App\Http\Controllers\Controller;
use App\Http\Requests\JobPostingRequest;
use App\Models\Company;
use App\Models\JobPosting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * 求人の管理(運営者による代行入稿)。立ち上げ期に掲載企業に代わって入力する(SPEC.md 11.6)。
 */
class JobPostingController extends Controller
{
    use ManagesJobPostings;

    public function index(Company $company): View
    {
        return $this->doIndex($company);
    }

    public function create(Company $company): View
    {
        return $this->doCreate($company);
    }

    public function store(JobPostingRequest $request, Company $company): RedirectResponse
    {
        return $this->doStore($request, $company);
    }

    public function edit(Company $company, JobPosting $jobPosting): View
    {
        return $this->doEdit($company, $jobPosting);
    }

    public function update(JobPostingRequest $request, Company $company, JobPosting $jobPosting): RedirectResponse
    {
        return $this->doUpdate($request, $company, $jobPosting);
    }

    public function destroy(Company $company, JobPosting $jobPosting): RedirectResponse
    {
        return $this->doDestroy($company, $jobPosting);
    }

    public function duplicate(Company $company, JobPosting $jobPosting): RedirectResponse
    {
        return $this->doDuplicate($company, $jobPosting);
    }

    public function submit(Company $company, JobPosting $jobPosting): RedirectResponse
    {
        return $this->doSubmit($company, $jobPosting);
    }

    protected function targetCompany(?Company $routeCompany = null): Company
    {
        return $routeCompany;
    }

    protected function viewPrefix(): string
    {
        return 'admin.job-postings';
    }

    protected function redirectRoute(Company $company): string
    {
        return route('admin.companies.job-postings.index', $company);
    }
}
