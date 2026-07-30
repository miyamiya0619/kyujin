<?php

namespace App\Services;

use App\Models\JobPosting;
use App\Models\JobPostingReview;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * 掲載企業が求人を審査に提出する。
 *
 * サイト設定で審査が OFF になっている場合は即座に公開する
 * (人材会社が自社で入稿する運用向け。SPEC.md 7章)。
 *
 * 提出(公開申請)は掲載プランの同時掲載件数の上限を超えられない
 * (SPEC.md 6.1)。上限判定は PostingPlanLimitService に集約している。
 */
class SubmitJobPostingForReviewService
{
    public function __construct(private readonly PostingPlanLimitService $limits) {}

    public function submit(JobPosting $jobPosting): JobPosting
    {
        $company = $jobPosting->company;

        if (! $this->limits->canSubmitJobPosting($company, excluding: $jobPosting)) {
            throw ValidationException::withMessages([
                'plan' => '掲載プランの同時掲載件数の上限に達しているため、これ以上公開申請できません。',
            ]);
        }

        DB::transaction(function () use ($jobPosting, $company) {
            if (SiteSetting::current()->requires_review) {
                $jobPosting->update(['status' => JobPosting::STATUS_PENDING]);

                return;
            }

            $jobPosting->update($this->limits->publishAttributes($company));

            JobPostingReview::create([
                'job_posting_id' => $jobPosting->id,
                'admin_user_id' => null,
                'action' => JobPostingReview::ACTION_AUTO_APPROVED,
            ]);
        });

        return $jobPosting->fresh();
    }
}
