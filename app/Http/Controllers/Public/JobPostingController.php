<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\EmploymentType;
use App\Models\FacilityType;
use App\Models\JobCategory;
use App\Models\JobFeature;
use App\Models\JobPosting;
use App\Models\Prefecture;
use App\Models\Qualification;
use App\Services\JobPostingSearchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 求人検索・詳細(求職者向け・公開ページ)。
 *
 * 表示に必要なデータは全てここで用意してビューに渡す。
 * テーマ側でクエリを書かせない(CLAUDE.md 3.2)。
 */
class JobPostingController extends Controller
{
    public function index(Request $request, JobPostingSearchService $search): View
    {
        return view('public.jobs.index', [
            'jobPostings' => $search->search($request),
            'prefectures' => Prefecture::selectable()->get(),
            'cities' => $request->filled('prefecture_id')
                ? City::selectable()->where('prefecture_id', $request->integer('prefecture_id'))->get()
                : collect(),
            'facilityTypes' => FacilityType::selectable()->get(),
            'jobCategories' => JobCategory::selectable()->get(),
            'employmentTypes' => EmploymentType::selectable()->get(),
            'qualifications' => Qualification::selectable()->get(),
            'jobFeatures' => JobFeature::selectable()->get(),
        ]);
    }

    public function show(JobPosting $jobPosting): View
    {
        // 審査待ち・下書き・掲載終了の求人 URL を直接叩かれても見せない。
        // 403 ではなく 404(存在自体を知らせない。CLAUDE.md 3.5 の延長)。
        if (! JobPosting::published()->whereKey($jobPosting->id)->exists()) {
            throw new NotFoundHttpException;
        }

        $jobPosting->load([
            'company', 'workplace.prefecture', 'workplace.city', 'workplace.facilityType',
            'jobCategory', 'employmentType', 'qualifications', 'features',
        ]);

        // 閲覧数はページ表示のたびに増やす。応募動線の効果測定に使う(SPEC.md 8.2)。
        //
        // ⚠ DB::table() は Query Builder であり、Eloquent 専用の whereKey() は
        //   存在しない。存在しないメソッドは __call() 経由で where('key', ...) の
        //   ような意図しないクエリになり、実行時まで気づけなかった(実際に発生)。
        //   Query Builder には素直に主キーのカラム名を書くこと。
        DB::table('job_postings')->where('id', $jobPosting->id)->increment('view_count');

        return view('public.jobs.show', [
            'jobPosting' => $jobPosting,
            'relatedJobPostings' => JobPosting::published()
                ->where('company_id', $jobPosting->company_id)
                ->whereKeyNot($jobPosting->id)
                ->with('workplace')
                ->limit(4)
                ->get(),
        ]);
    }
}
