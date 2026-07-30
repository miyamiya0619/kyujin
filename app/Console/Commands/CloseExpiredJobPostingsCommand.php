<?php

namespace App\Console\Commands;

use App\Models\JobPosting;
use Illuminate\Console\Command;

/**
 * 掲載期間(掲載プランの `posting_duration_days` から計算した `expires_at`)を
 * 過ぎた求人を自動的に掲載終了にする(SPEC.md 6.1 / 7章)。
 *
 * cron から毎日 1 回実行する想定。共有レンタルサーバーの実行時間上限を
 * 考慮し、チャンク処理で更新する(CLAUDE.md 4章)。
 */
class CloseExpiredJobPostingsCommand extends Command
{
    protected $signature = 'job-postings:close-expired';

    protected $description = '掲載期限を過ぎた公開中の求人を自動で掲載終了にする';

    public function handle(): int
    {
        $count = 0;

        JobPosting::query()
            ->where('status', JobPosting::STATUS_PUBLISHED)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->chunkById(200, function ($jobPostings) use (&$count) {
                foreach ($jobPostings as $jobPosting) {
                    $jobPosting->update(['status' => JobPosting::STATUS_CLOSED]);
                    $count++;
                }
            });

        $this->info("{$count} 件の求人を掲載終了にしました。");

        return self::SUCCESS;
    }
}
