<?php

namespace App\Services;

use App\Models\JobPosting;
use App\Models\JobPostingReview;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\DB;

/**
 * 掲載企業が求人を審査に提出する。
 *
 * サイト設定で審査が OFF になっている場合は即座に公開する
 * (人材会社が自社で入稿する運用向け。SPEC.md 7章)。
 */
class SubmitJobPostingForReviewService
{
    public function submit(JobPosting $jobPosting): JobPosting
    {
        DB::transaction(function () use ($jobPosting) {
            if (SiteSetting::current()->requires_review) {
                $jobPosting->update(['status' => JobPosting::STATUS_PENDING]);

                return;
            }

            $jobPosting->update([
                'status' => JobPosting::STATUS_PUBLISHED,
                'published_at' => $jobPosting->published_at ?? now(),
            ]);

            JobPostingReview::create([
                'job_posting_id' => $jobPosting->id,
                'admin_user_id' => null,
                'action' => JobPostingReview::ACTION_AUTO_APPROVED,
            ]);
        });

        return $jobPosting->fresh();
    }
}
