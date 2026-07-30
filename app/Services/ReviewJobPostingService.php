<?php

namespace App\Services;

use App\Models\AdminUser;
use App\Models\JobPosting;
use App\Models\JobPostingReview;
use App\Notifications\JobPostingRejectedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * 運営者による求人審査(承認・差戻し)。SPEC.md 7章の中核。
 *
 * 審査対象は必ず「審査待ち」の求人に限る。下書きや公開中の求人を
 * 誤って審査してしまわないようにするため。
 *
 * ⚠ 承認時は掲載プランの同時掲載件数の上限を強制チェックしない。
 *   上限は「掲載企業が公開申請できるか」(SubmitJobPostingForReviewService)の
 *   関門であり、審査待ちに複数件が並んでいる状況で運営者がどれを通すかは
 *   運営者の裁量に委ねる。強制ブロックすると柔軟な運用ができなくなる。
 */
class ReviewJobPostingService
{
    public function __construct(private readonly PostingPlanLimitService $limits) {}

    public function approve(JobPosting $jobPosting, AdminUser $admin): JobPosting
    {
        $this->ensurePending($jobPosting);

        DB::transaction(function () use ($jobPosting, $admin) {
            $jobPosting->update($this->limits->publishAttributes($jobPosting->company));

            JobPostingReview::create([
                'job_posting_id' => $jobPosting->id,
                'admin_user_id' => $admin->id,
                'action' => JobPostingReview::ACTION_APPROVED,
            ]);
        });

        return $jobPosting->fresh();
    }

    public function reject(JobPosting $jobPosting, AdminUser $admin, string $reason): JobPosting
    {
        $this->ensurePending($jobPosting);

        DB::transaction(function () use ($jobPosting, $admin, $reason) {
            $jobPosting->update(['status' => JobPosting::STATUS_REJECTED]);

            JobPostingReview::create([
                'job_posting_id' => $jobPosting->id,
                'admin_user_id' => $admin->id,
                'action' => JobPostingReview::ACTION_REJECTED,
                'comment' => $reason,
            ]);
        });

        // 掲載企業の全担当者へ通知する(誰が対応するかは企業側の運用に委ねる)
        foreach ($jobPosting->company->users as $companyUser) {
            $companyUser->notify(new JobPostingRejectedNotification($jobPosting, $reason));
        }

        return $jobPosting->fresh();
    }

    private function ensurePending(JobPosting $jobPosting): void
    {
        if (! $jobPosting->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'この求人は審査待ちの状態ではないため、審査できません。',
            ]);
        }
    }
}
