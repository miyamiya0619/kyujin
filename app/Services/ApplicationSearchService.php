<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

/**
 * 応募者一覧の絞り込み(TASKS.md T-13)。
 *
 * 掲載企業側(`$company` を渡す)と運営者の横断確認画面(`$company` を渡さず全社対象)の
 * 両方から使う。絞り込み条件: ステータス・求人・事業所・応募日・流入元。
 */
class ApplicationSearchService
{
    public function search(Request $request, ?Company $company = null): Collection
    {
        return Application::query()
            ->when($company, fn ($q) => $q->where('company_id', $company->id))
            ->when(! $company && $request->filled('company_id'), fn ($q) => $q
                ->where('company_id', $request->integer('company_id')))
            ->when($request->filled('status'), fn ($q) => $q
                ->where('status', $request->string('status')->toString()))
            ->when($request->filled('job_posting_id'), fn ($q) => $q
                ->where('job_posting_id', $request->integer('job_posting_id')))
            ->when($request->filled('workplace_id'), fn ($q) => $q
                ->whereHas('jobPosting', fn ($jp) => $jp->where('workplace_id', $request->integer('workplace_id'))))
            ->when($request->filled('referrer_source'), fn ($q) => $q
                ->where('referrer_source', $request->string('referrer_source')->toString()))
            ->when($request->filled('applied_from'), fn ($q) => $q
                ->whereDate('applied_at', '>=', $request->date('applied_from')))
            ->when($request->filled('applied_to'), fn ($q) => $q
                ->whereDate('applied_at', '<=', $request->date('applied_to')))
            ->with(['jobPosting.workplace', 'jobSeeker', 'company'])
            ->orderByDesc('applied_at')
            ->get();
    }
}
