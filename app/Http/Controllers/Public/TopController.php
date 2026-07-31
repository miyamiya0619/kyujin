<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Models\Prefecture;
use Illuminate\Contracts\View\View;

/**
 * 公開メディアのトップページ。
 *
 * 表示に必要なデータは全てここで用意してビューに渡す。
 * テーマ側でデータを取りに行かせてはいけない(CLAUDE.md 3.2)。
 */
class TopController extends Controller
{
    public function __invoke(): View
    {
        $baseQuery = JobPosting::published()
            ->with(['company', 'workplace.prefecture', 'workplace.city', 'jobCategory', 'employmentType']);

        return view('public.top', [
            'featuredJobPostings' => (clone $baseQuery)->where('is_featured', true)->limit(6)->get(),
            'newJobPostings' => (clone $baseQuery)->orderByDesc('published_at')->limit(8)->get(),
            'prefectures' => Prefecture::selectable()->get(),
            'totalJobPostingCount' => JobPosting::published()->count(),
        ]);
    }
}
