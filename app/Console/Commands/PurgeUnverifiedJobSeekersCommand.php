<?php

namespace App\Console\Commands;

use App\Models\JobSeeker;
use Illuminate\Console\Command;

/**
 * メール認証が済まないまま長時間放置された求職者の仮登録を削除する(TASKS.md T-27)。
 *
 * これにより、認証しないまま離脱した場合でも同じメールアドレスで再登録できる。
 *
 * 応募済み(applications が1件でもある)求職者は対象から除外する。
 * 未ログインの求職者は会員登録と同時に応募できる仕様(T-12)のため、
 * 認証前でも実際の応募が存在しうる。`applications.job_seeker_id` は
 * nullOnDelete(T-17)なので削除しても応募・スナップショット自体は残るが、
 * 一度応募まで進んだ相手のアカウントをただの未認証扱いで消してしまうと、
 * 後で認証してももう繋がりが無い(job_seeker_id が null になる)別アカウントに
 * なってしまう。実際に使った形跡がある相手は残す。
 *
 * cron から毎日 1 回実行する想定(CloseExpiredJobPostingsCommand と同じ運用)。
 */
class PurgeUnverifiedJobSeekersCommand extends Command
{
    protected $signature = 'job-seekers:purge-unverified';

    protected $description = 'メール認証が24時間以内に完了しなかった求職者の仮登録を削除する';

    public function handle(): int
    {
        $count = 0;

        JobSeeker::query()
            ->whereNull('email_verified_at')
            ->where('created_at', '<', now()->subHours(24))
            ->whereDoesntHave('applications')
            ->chunkById(200, function ($jobSeekers) use (&$count) {
                foreach ($jobSeekers as $jobSeeker) {
                    $jobSeeker->delete();
                    $count++;
                }
            });

        $this->info("{$count} 件の未認証の仮登録を削除しました。");

        return self::SUCCESS;
    }
}
