<?php

namespace App\Services;

use App\Models\Company;
use App\Models\JobPosting;
use App\Models\PostingPlan;
use Illuminate\Support\Carbon;

/**
 * 掲載企業に割り当てられたプランの上限判定(SPEC.md 6.1)。
 *
 * 「同時掲載可能件数」は公開申請(審査待ち + 公開中)の件数でカウントする。
 * 下書きはカウントしない。何件でも下書きを作れてよく、公開しようとした
 * 瞬間に上限へ達しているかどうかを問うのが SPEC の意図であるため。
 */
class PostingPlanLimitService
{
    /**
     * 現在有効なプラン。割当が無ければ制限なし(null)として扱う。
     * 立ち上げ期にプラン未設定の企業が求人を作れなくなるのを防ぐため。
     */
    public function currentPlan(Company $company): ?PostingPlan
    {
        $assignment = $company->planAssignments()->active()->orderByDesc('starts_at')->first();

        return $assignment?->postingPlan;
    }

    /**
     * 求人を公開申請してよいか。上限に達していなければ true。
     * 編集中の求人自身は数えない(再提出で自分自身が上限に触れないようにする)。
     */
    public function canSubmitJobPosting(Company $company, ?JobPosting $excluding = null): bool
    {
        $max = $this->currentPlan($company)?->max_job_postings;

        if ($max === null) {
            return true;
        }

        return $this->activeJobPostingCount($company, $excluding) < $max;
    }

    /**
     * 残り何件公開申請できるか。無制限なら null。
     */
    public function remainingJobPostingSlots(Company $company): ?int
    {
        $max = $this->currentPlan($company)?->max_job_postings;

        if ($max === null) {
            return null;
        }

        return max(0, $max - $this->activeJobPostingCount($company));
    }

    public function canAddWorkplace(Company $company): bool
    {
        $max = $this->currentPlan($company)?->max_workplaces;

        if ($max === null) {
            return true;
        }

        return $company->workplaces()->count() < $max;
    }

    public function remainingWorkplaceSlots(Company $company): ?int
    {
        $max = $this->currentPlan($company)?->max_workplaces;

        if ($max === null) {
            return null;
        }

        return max(0, $max - $company->workplaces()->count());
    }

    /** プランが持つ掲載期間(日数)。null は無期限。 */
    public function postingDurationDays(Company $company): ?int
    {
        return $this->currentPlan($company)?->posting_duration_days;
    }

    /** プランが上位表示を許可しているか。 */
    public function isFeatured(Company $company): bool
    {
        return (bool) $this->currentPlan($company)?->is_featured;
    }

    /**
     * 求人を公開状態にする際に設定すべき属性。
     * 承認(ReviewJobPostingService)・自動承認(SubmitJobPostingForReviewService)の
     * 双方で同じ計算をするため、ここに集約する。
     *
     * @return array{status: string, published_at: Carbon, expires_at: ?Carbon, is_featured: bool}
     */
    public function publishAttributes(Company $company): array
    {
        $plan = $this->currentPlan($company);
        $publishedAt = now();

        return [
            'status' => JobPosting::STATUS_PUBLISHED,
            'published_at' => $publishedAt,
            'expires_at' => $plan?->posting_duration_days
                ? $publishedAt->clone()->addDays($plan->posting_duration_days)
                : null,
            'is_featured' => (bool) $plan?->is_featured,
        ];
    }

    private function activeJobPostingCount(Company $company, ?JobPosting $excluding = null): int
    {
        return $company->jobPostings()
            ->whereIn('status', [JobPosting::STATUS_PENDING, JobPosting::STATUS_PUBLISHED])
            ->when($excluding, fn ($q) => $q->whereKeyNot($excluding->id))
            ->count();
    }
}
