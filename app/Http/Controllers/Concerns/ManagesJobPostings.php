<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Requests\JobPostingRequest;
use App\Models\Company;
use App\Models\EmploymentType;
use App\Models\JobCategory;
use App\Models\JobFeature;
use App\Models\JobPosting;
use App\Models\Qualification;
use App\Models\SiteSetting;
use App\Services\SubmitJobPostingForReviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 求人 CRUD の共通処理。運営者(代行入稿)・掲載企業(セルフ入稿)の双方で使う。
 *
 * ⚠ 公開アクション(index/edit など)は各コントローラで URL のパラメータ順に
 *   合わせて個別定義し、実処理だけをここの `do*` メソッドに委譲すること。
 *   トレイトの公開メソッドを複数のルート形状に直接晒すと、Laravel の暗黙の
 *   ルートモデルバインディングが引数の型ではなく URL 登録順の位置で解決するため、
 *   型が合っていても位置がずれて TypeError になる(T-06 で実際に発生。CLAUDE.md 3.9)。
 */
trait ManagesJobPostings
{
    /**
     * 操作対象の企業。運営者側は URL のパラメータ、掲載企業側は
     * ログイン中の担当者の所属企業を返す(ManagesWorkplaces と同じ方針)。
     */
    abstract protected function targetCompany(?Company $routeCompany = null): Company;

    abstract protected function viewPrefix(): string;

    abstract protected function redirectRoute(Company $company): string;

    protected function doIndex(?Company $routeCompany): View
    {
        $company = $this->targetCompany($routeCompany);

        return view("{$this->viewPrefix()}.index", [
            'company' => $company,
            'jobPostings' => $company->jobPostings()
                ->with('workplace', 'jobCategory', 'employmentType', 'reviews')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    protected function doCreate(?Company $routeCompany): View
    {
        $company = $this->targetCompany($routeCompany);

        return view("{$this->viewPrefix()}.create", [
            'company' => $company,
            'jobPosting' => new JobPosting,
            'selectedQualificationIds' => [],
            'selectedFeatureIds' => [],
            ...$this->formOptions($company),
        ]);
    }

    protected function doStore(JobPostingRequest $request, ?Company $routeCompany): RedirectResponse
    {
        $company = $this->targetCompany($routeCompany);

        if (! SiteSetting::current()->canAddMoreJobPostings()) {
            throw ValidationException::withMessages([
                'plan' => '契約プランの登録可能求人数の上限に達しているため、これ以上求人を登録できません。',
            ]);
        }

        $this->ensureWorkplaceBelongsTo($company, $request->integer('workplace_id'));

        $jobPosting = DB::transaction(function () use ($request, $company) {
            $jobPosting = new JobPosting($request->safe()->except(['qualification_ids', 'feature_ids']));
            $jobPosting->company_id = $company->id;
            $jobPosting->status = JobPosting::STATUS_DRAFT;
            $jobPosting->save();

            $jobPosting->qualifications()->sync($request->input('qualification_ids', []));
            $jobPosting->features()->sync($request->input('feature_ids', []));

            return $jobPosting;
        });

        return redirect($this->redirectRoute($company))->with('status', "求人「{$jobPosting->title}」を下書きに保存しました。");
    }

    protected function doEdit(?Company $routeCompany, JobPosting $jobPosting): View
    {
        $company = $this->targetCompany($routeCompany);
        $this->ensureBelongsTo($company, $jobPosting);

        return view("{$this->viewPrefix()}.edit", [
            'company' => $company,
            'jobPosting' => $jobPosting,
            'selectedQualificationIds' => $jobPosting->qualifications()->pluck('qualifications.id')->all(),
            'selectedFeatureIds' => $jobPosting->features()->pluck('job_features.id')->all(),
            ...$this->formOptions($company),
        ]);
    }

    protected function doUpdate(JobPostingRequest $request, ?Company $routeCompany, JobPosting $jobPosting): RedirectResponse
    {
        $company = $this->targetCompany($routeCompany);
        $this->ensureBelongsTo($company, $jobPosting);
        $this->ensureWorkplaceBelongsTo($company, $request->integer('workplace_id'));

        DB::transaction(function () use ($request, $jobPosting) {
            $jobPosting->fill($request->safe()->except(['qualification_ids', 'feature_ids']));

            // 公開中の求人を編集したら審査待ちに戻す。承認済みの内容と違うものが
            // 公開され続けるのを防ぐ(SPEC.md 7章 / T-08 で審査フローと合わせて検証する)。
            if ($jobPosting->getOriginal('status') === JobPosting::STATUS_PUBLISHED) {
                $jobPosting->status = JobPosting::STATUS_PENDING;
            }

            $jobPosting->save();

            $jobPosting->qualifications()->sync($request->input('qualification_ids', []));
            $jobPosting->features()->sync($request->input('feature_ids', []));
        });

        return redirect($this->redirectRoute($company))->with('status', '求人情報を更新しました。');
    }

    protected function doDestroy(?Company $routeCompany, JobPosting $jobPosting): RedirectResponse
    {
        $company = $this->targetCompany($routeCompany);
        $this->ensureBelongsTo($company, $jobPosting);

        $jobPosting->delete();

        return redirect($this->redirectRoute($company))->with('status', "求人「{$jobPosting->title}」を削除しました。");
    }

    /**
     * 既存求人を複製して下書きを作る。
     * 施設ごと・職種ごとに似た求人を作る現場の実態に合わせた機能(SPEC.md 11.6)。
     */
    protected function doDuplicate(?Company $routeCompany, JobPosting $jobPosting): RedirectResponse
    {
        $company = $this->targetCompany($routeCompany);
        $this->ensureBelongsTo($company, $jobPosting);

        $duplicate = DB::transaction(function () use ($jobPosting) {
            $copy = $jobPosting->replicate([
                'status', 'published_at', 'expires_at',
                'view_count', 'application_count',
            ]);
            $copy->title = "{$jobPosting->title}(コピー)";
            $copy->status = JobPosting::STATUS_DRAFT;
            $copy->published_at = null;
            $copy->expires_at = null;
            $copy->view_count = 0;
            $copy->application_count = 0;
            $copy->save();

            $copy->qualifications()->sync($jobPosting->qualifications()->pluck('qualifications.id'));
            $copy->features()->sync($jobPosting->features()->pluck('job_features.id'));

            return $copy;
        });

        return redirect($this->redirectRoute($company))->with('status', "「{$jobPosting->title}」を複製しました。内容を確認して公開申請してください。");
    }

    /**
     * 求人を審査に提出する。下書き・差戻しの状態からのみ提出できる。
     * サイト設定で審査が OFF の場合は即座に公開される(SubmitJobPostingForReviewService)。
     */
    protected function doSubmit(?Company $routeCompany, JobPosting $jobPosting): RedirectResponse
    {
        $company = $this->targetCompany($routeCompany);
        $this->ensureBelongsTo($company, $jobPosting);

        if (! in_array($jobPosting->status, [JobPosting::STATUS_DRAFT, JobPosting::STATUS_REJECTED], true)) {
            throw ValidationException::withMessages([
                'status' => 'この求人は提出できる状態ではありません。',
            ]);
        }

        app(SubmitJobPostingForReviewService::class)->submit($jobPosting);

        $message = $jobPosting->fresh()->isPublished()
            ? "「{$jobPosting->title}」を公開しました。"
            : "「{$jobPosting->title}」を審査に提出しました。";

        return redirect($this->redirectRoute($company))->with('status', $message);
    }

    /**
     * URL を組み替えて他社の求人を操作されるのを防ぐ。
     * 403 ではなく 404 を返す(存在自体を知らせない)。
     */
    private function ensureBelongsTo(Company $company, JobPosting $jobPosting): void
    {
        if ($jobPosting->company_id !== $company->id) {
            throw new NotFoundHttpException;
        }
    }

    /**
     * 求人が参照する事業所が自社のものかを確認する。
     * 他社の事業所 ID を指定されても求人を紐づけさせない。
     */
    private function ensureWorkplaceBelongsTo(Company $company, int $workplaceId): void
    {
        $belongs = $company->workplaces()->whereKey($workplaceId)->exists();

        if (! $belongs) {
            throw new NotFoundHttpException;
        }
    }

    /**
     * 入力フォームに必要な選択肢一式。
     * マスタは必ず `selectable()` を通す(CLAUDE.md 3.7)。
     *
     * @return array<string, mixed>
     */
    private function formOptions(Company $company): array
    {
        return [
            'workplaces' => $company->workplaces()->orderBy('id')->get(),
            'jobCategories' => JobCategory::selectable()->get(),
            'employmentTypes' => EmploymentType::selectable()->get(),
            'qualifications' => Qualification::selectable()->get(),
            'jobFeatures' => JobFeature::selectable()->get(),
        ];
    }
}
