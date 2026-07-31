<?php

namespace App\Services;

use App\Models\JobPosting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
 * 公開メディアの求人検索(SPEC.md 5.1)。
 *
 * 絞り込み条件: 都道府県・市区町村・職種・資格・施設形態・雇用形態・夜勤有無・こだわり条件。
 * **検索の起点は必ず `JobPosting::published()` を通す**(CLAUDE.md 3.5)。
 */
class JobPostingSearchService
{
    private const PER_PAGE = 20;

    public function search(Request $request): LengthAwarePaginator
    {
        $query = JobPosting::query()
            ->published()
            ->with(['company', 'workplace.prefecture', 'workplace.city', 'workplace.facilityType', 'jobCategory', 'employmentType'])
            ->when($request->filled('prefecture_id'), fn ($q) => $q
                ->whereHas('workplace', fn ($w) => $w->where('prefecture_id', $request->integer('prefecture_id'))))
            ->when($request->filled('city_id'), fn ($q) => $q
                ->whereHas('workplace', fn ($w) => $w->where('city_id', $request->integer('city_id'))))
            ->when($request->filled('facility_type_id'), fn ($q) => $q
                ->whereHas('workplace', fn ($w) => $w->where('facility_type_id', $request->integer('facility_type_id'))))
            ->when($request->filled('job_category_id'), fn ($q) => $q
                ->where('job_category_id', $request->integer('job_category_id')))
            ->when($request->filled('employment_type_id'), fn ($q) => $q
                ->where('employment_type_id', $request->integer('employment_type_id')))
            ->when($request->boolean('night_shift_only'), fn ($q) => $q
                ->where('has_night_shift', true))
            ->when($request->filled('qualification_id'), fn ($q) => $q
                ->whereHas('qualifications', fn ($sub) => $sub->whereKey($request->integer('qualification_id'))))
            ->when($request->filled('feature_ids'), function ($q) use ($request) {
                foreach ((array) $request->input('feature_ids') as $featureId) {
                    // こだわり条件は AND 条件にする。1 つでも複数選ばれたら
                    // 「全部満たす求人」に絞り込むのが求職者の直感に合う。
                    $q->whereHas('features', fn ($sub) => $sub->whereKey((int) $featureId));
                }
            })
            ->when($request->filled('keyword'), function ($q) use ($request) {
                $keyword = $request->string('keyword')->toString();
                $q->where(fn ($sub) => $sub
                    ->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%"));
            })
            // 上位表示(掲載プランの is_featured)を優先し、次に新着順。
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at');

        return $query->paginate(self::PER_PAGE)->withQueryString();
    }
}
