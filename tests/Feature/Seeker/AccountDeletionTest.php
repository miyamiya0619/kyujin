<?php

namespace Tests\Feature\Seeker;

use App\Models\Application;
use App\Models\ApplicationResumeSnapshot;
use App\Models\JobPosting;
use App\Models\JobSeeker;
use App\Models\Qualification;
use App\Notifications\AccountDeletedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_退会確認画面が表示される(): void
    {
        $jobSeeker = JobSeeker::factory()->create(['email' => 'taiin@example.com']);

        $this->actingAs($jobSeeker, 'seeker')
            ->get(route('seeker.account.confirm'))
            ->assertOk()
            ->assertSee('taiin@example.com');
    }

    public function test_未認証でも退会確認画面に入れる(): void
    {
        $jobSeeker = JobSeeker::factory()->unverified()->create();

        $this->actingAs($jobSeeker, 'seeker')
            ->get(route('seeker.account.confirm'))
            ->assertOk();
    }

    public function test_未ログインでは退会確認画面に入れない(): void
    {
        $this->get(route('seeker.account.confirm'))->assertRedirect(route('seeker.login'));
    }

    public function test_退会するとアカウントが削除されログアウトする(): void
    {
        $jobSeeker = JobSeeker::factory()->create();

        $this->actingAs($jobSeeker, 'seeker')
            ->delete(route('seeker.account.destroy'))
            ->assertRedirect(route('public.top'));

        $this->assertModelMissing($jobSeeker);
        $this->assertGuest('seeker');
    }

    public function test_退会すると完了メールが送信される(): void
    {
        Notification::fake();

        $jobSeeker = JobSeeker::factory()->create();

        $this->actingAs($jobSeeker, 'seeker')->delete(route('seeker.account.destroy'));

        Notification::assertSentTo($jobSeeker, AccountDeletedNotification::class);
    }

    public function test_退会すると保有資格と職務経歴も一緒に削除される(): void
    {
        $this->seed();

        $jobSeeker = JobSeeker::factory()->create();
        $jobSeeker->experiences()->create(['organization_name' => 'テスト事業所']);
        $qualificationId = Qualification::selectable()->first()->id;
        $jobSeeker->qualifications()->attach($qualificationId);

        $this->actingAs($jobSeeker, 'seeker')->delete(route('seeker.account.destroy'));

        $this->assertDatabaseMissing('job_seeker_experiences', ['job_seeker_id' => $jobSeeker->id]);
        $this->assertDatabaseMissing('job_seeker_qualification', ['job_seeker_id' => $jobSeeker->id]);
    }

    public function test_退会しても応募済みの選考記録は掲載企業側に残る(): void
    {
        $jobSeeker = JobSeeker::factory()->create();
        $jobPosting = JobPosting::factory()->published()->create();
        $application = Application::factory()->create([
            'job_posting_id' => $jobPosting->id,
            'job_seeker_id' => $jobSeeker->id,
            'company_id' => $jobPosting->company_id,
        ]);
        $snapshot = ApplicationResumeSnapshot::create([
            'application_id' => $application->id,
            'payload' => ['name' => $jobSeeker->name],
            'snapshot_at' => now(),
        ]);

        $this->actingAs($jobSeeker, 'seeker')->delete(route('seeker.account.destroy'));

        $this->assertModelExists($application);
        $this->assertModelExists($snapshot);
        $this->assertNull($application->fresh()->job_seeker_id, '求職者への参照だけが外れる');
    }

    public function test_未ログインでは退会できない(): void
    {
        $this->delete(route('seeker.account.destroy'))->assertRedirect(route('seeker.login'));
    }
}
