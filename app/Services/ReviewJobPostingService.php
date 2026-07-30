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
 */
class ReviewJobPostingService
{
    public function approve(JobPosting $jobPosting, AdminUser $admin): JobPosting
    {
        $this->ensurePending($jobPosting);

        DB::transaction(function () use ($jobPosting, $admin) {
            $jobPosting->update([
                'status' => JobPosting::STATUS_PUBLISHED,
                'published_at' => $jobPosting->published_at ?? now(),
            ]);

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
