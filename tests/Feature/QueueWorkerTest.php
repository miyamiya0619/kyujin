<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\JobPosting;
use App\Models\JobSeeker;
use App\Notifications\ApplicationReceivedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * キューは cron から毎分 `queue:work --stop-when-empty` を実行する運用であり、
 * 常駐ワーカーを前提にしない(CLAUDE.md 4章 / TASKS.md T-15)。
 *
 * ここでは `QUEUE_CONNECTION=database` に切り替えて実際のキュー機構を使い、
 * 「ワーカーが動いていなくても業務処理自体は成功すること」と
 * 「失敗したジョブが failed_jobs に記録され、再実行できること」を確認する。
 */
class QueueWorkerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['queue.default' => 'database']);
    }

    public function test_ワーカーが動いていなくても応募自体は成功する(): void
    {
        $jobPosting = JobPosting::factory()->published()->create();
        $jobSeeker = JobSeeker::factory()->create();

        $this->actingAs($jobSeeker, 'seeker')
            ->post(route('public.jobs.apply.store', $jobPosting), [])
            ->assertRedirect(route('seeker.mypage'));

        $this->assertDatabaseHas('applications', [
            'job_posting_id' => $jobPosting->id,
            'job_seeker_id' => $jobSeeker->id,
        ]);

        // 通知はキューに積まれるだけで、まだ送信されていない(ワーカー未実行)。
        $this->assertDatabaseCount('jobs', 1);
    }

    public function test_失敗したジョブはfailed_jobsに記録され再実行できる(): void
    {
        // 何も listen していないポートに向けることで、実際に接続失敗させる
        // (DNS 解決を伴わない 127.0.0.1 宛のため、タイムアウトを待たず即座に失敗する)。
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '127.0.0.1',
            'mail.mailers.smtp.port' => 1,
        ]);

        $jobSeeker = JobSeeker::factory()->create();
        $application = Application::factory()->create(['job_seeker_id' => $jobSeeker->id]);

        $jobSeeker->notify(new ApplicationReceivedNotification($application));

        $this->assertDatabaseCount('jobs', 1);
        $this->assertDatabaseCount('failed_jobs', 0);

        $this->artisan('queue:work', [
            '--stop-when-empty' => true,
            '--tries' => 1,
        ])->run();

        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('failed_jobs', 1);

        // メール設定を復旧すれば再実行できる(mailpit 等の到達可能な宛先)。
        config([
            'mail.mailers.smtp.host' => 'mailpit',
            'mail.mailers.smtp.port' => 1025,
        ]);

        $this->artisan('queue:retry', ['id' => ['all']])->run();

        // 再実行でジョブが jobs テーブルに戻ること
        $this->assertDatabaseCount('jobs', 1);
    }
}
