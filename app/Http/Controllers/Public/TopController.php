<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\EmploymentType;
use App\Models\FacilityType;
use App\Models\JobCategory;
use App\Models\JobFeature;
use App\Models\JobPosting;
use App\Models\Prefecture;
use App\Models\Qualification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

/**
 * 公開メディアのトップページ。
 *
 * 表示に必要なデータは全てここで用意してビューに渡す。
 * テーマ側でデータを取りに行かせてはいけない(CLAUDE.md 3.2)。
 *
 * 「◯◯から探す」の各セクションは求人件数を添えて出す。件数が見えると
 * 求職者が空振りするリンクを踏まなくなり、内部リンクとしての SEO 価値も上がる。
 * 件数は条件ごとに 1 回の集約クエリでまとめて取り、リンクごとに数えない。
 */
class TopController extends Controller
{
    public function __invoke(): View
    {
        $baseQuery = JobPosting::published()
            ->with(['company', 'workplace.prefecture', 'workplace.city', 'jobCategory', 'employmentType']);

        $employmentTypes = EmploymentType::selectable()->get();
        $employmentTypeCounts = $this->countByOwnColumn('employment_type_id');

        return view('public.top', [
            'featuredJobPostings' => (clone $baseQuery)->where('is_featured', true)->limit(6)->get(),
            'newJobPostings' => (clone $baseQuery)->orderByDesc('published_at')->limit(8)->get(),
            'totalJobPostingCount' => JobPosting::published()->count(),
            'nightShiftCount' => JobPosting::published()->where('has_night_shift', true)->count(),

            // 検索フォーム用(都道府県は「エリアから探す」でも使う)
            'prefectures' => Prefecture::selectable()->get(),

            // 「エリアから探す」。地方区分ごとにまとめて出す。
            'prefectureGroups' => Prefecture::selectable()->get()->groupBy('region'),
            'prefectureCounts' => $this->countByWorkplaceColumn('prefecture_id'),

            'jobCategories' => JobCategory::selectable()->get(),
            'jobCategoryCounts' => $this->countByOwnColumn('job_category_id'),

            'facilityTypes' => FacilityType::selectable()->get(),
            'facilityTypeCounts' => $this->countByWorkplaceColumn('facility_type_id'),

            'employmentTypes' => $employmentTypes,
            'employmentTypeCounts' => $employmentTypeCounts,

            'qualifications' => Qualification::selectable()->get(),

            'jobFeatures' => JobFeature::selectable()->get(),
            'jobFeatureCounts' => $this->countByPivot('job_posting_feature', 'job_feature_id'),

            // 特集タイルに出す雇用形態(並び順の上位 2 件)。
            // 「正社員」「パート」のような特定の値をコードに書かない(顧客ごとに
            // 有効なマスタが違うため。CLAUDE.md 3.1 / 3.7)。
            'featuredEmploymentTypes' => $employmentTypes->take(2),

            /*
             * 以下は器だけ用意してあるセクション。対応する機能が未実装のため
             * 現時点では必ず空で、ビュー側は空なら丸ごと描画しない。
             * 機能を作ったらここに詰めるだけでトップに出るようにしてある。
             *   - $columnArticles ... 介護のお役立ちコラム(CMS が未実装)
             *   - $seekerVoices   ... 利用者の声(投稿・掲載許諾の仕組みが未実装)
             * ⚠ 架空の体験談・記事をダミーとして埋めないこと。そのまま公開されると
             *   実在しない実績を掲げることになる。
             */
            'columnArticles' => new Collection,
            'seekerVoices' => new Collection,
        ]);
    }

    /**
     * `job_postings` 自身の列でグループ化した公開求人の件数。
     *
     * @param  string  $column  呼び出し側がリテラルで渡す列名(利用者入力を渡さないこと)
     * @return Collection<int, int> 列の値 => 件数
     */
    private function countByOwnColumn(string $column): Collection
    {
        return JobPosting::published()
            ->whereNotNull($column)
            ->groupBy($column)
            ->selectRaw("{$column} as group_key, COUNT(*) as aggregate")
            ->pluck('aggregate', 'group_key');
    }

    /**
     * 事業所(`workplaces`)の列でグループ化した公開求人の件数。
     *
     * @param  string  $column  呼び出し側がリテラルで渡す列名(利用者入力を渡さないこと)
     * @return Collection<int, int> 列の値 => 件数
     */
    private function countByWorkplaceColumn(string $column): Collection
    {
        return JobPosting::published()
            ->join('workplaces', 'workplaces.id', '=', 'job_postings.workplace_id')
            ->whereNotNull("workplaces.{$column}")
            ->groupBy("workplaces.{$column}")
            ->selectRaw("workplaces.{$column} as group_key, COUNT(*) as aggregate")
            ->pluck('aggregate', 'group_key');
    }

    /**
     * 中間テーブル越しにグループ化した公開求人の件数(こだわり条件など)。
     *
     * 起点は必ず `published()` を通す。中間テーブルから直接数えると
     * 審査前・掲載終了の求人まで件数に混ざる(CLAUDE.md 3.5)。
     *
     * @param  string  $pivotTable  呼び出し側がリテラルで渡す中間テーブル名
     * @param  string  $foreignKey  呼び出し側がリテラルで渡す集計対象の外部キー
     * @return Collection<int, int> 外部キーの値 => 件数
     */
    private function countByPivot(string $pivotTable, string $foreignKey): Collection
    {
        return JobPosting::published()
            ->join($pivotTable, "{$pivotTable}.job_posting_id", '=', 'job_postings.id')
            ->groupBy("{$pivotTable}.{$foreignKey}")
            ->selectRaw("{$pivotTable}.{$foreignKey} as group_key, COUNT(*) as aggregate")
            ->pluck('aggregate', 'group_key');
    }
}
