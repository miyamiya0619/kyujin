<?php

namespace Tests\Feature\Public;

use App\Models\Application;
use App\Models\JobPosting;
use App\Models\JobSeeker;
use App\Models\Qualification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 求人への応募(TASKS.md T-12)。
 *
 * **応募時の履歴書はスナップショット。** 応募後にプロフィールを変えても
 * 内容が変わらないことが CLAUDE.md 3.6 の絶対ルール。
 */
class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ログイン済みの求職者が応募できる(): void
    {
        $jobPosting = JobPosting::factory()->published()->create();
        $jobSeeker = JobSeeker::factory()->create();

        $this->actingAs($jobSeeker, 'seeker')
            ->post(route('public.jobs.apply.store', $jobPosting), ['message' => 'よろしくお願いします。'])
            ->assertRedirect(route('seeker.mypage'));

        $this->assertDatabaseHas('applications', [
            'job_posting_id' => $jobPosting->id,
            'job_seeker_id' => $jobSeeker->id,
            'company_id' => $jobPosting->company_id,
            'status' => Application::STATUS_NEW,
            'message' => 'よろしくお願いします。',
            'referrer_source' => 'direct',
        ]);

        $this->assertSame(1, $jobPosting->fresh()->application_count);
    }

    public function test_応募後にプロフィールを変更してもスナップショットは変わらない(): void
    {
        $this->seed();

        $jobPosting = JobPosting::factory()->published()->create();
        $jobSeeker = JobSeeker::factory()->create(['name' => '応募時の名前', 'tel' => '090-0000-0000']);
        $qualification = Qualification::selectable()->first();
        $jobSeeker->qualifications()->attach($qualification->id);
        $jobSeeker->experiences()->create([
            'organization_name' => '応募時の事業所',
            'job_title' => '介護職員',
            'started_on' => '2020-04-01',
        ]);

        $this->actingAs($jobSeeker, 'seeker')
            ->post(route('public.jobs.apply.store', $jobPosting), []);

        $application = Application::where('job_seeker_id', $jobSeeker->id)->firstOrFail();
        $snapshot = $application->resumeSnapshot;

        $this->assertSame('応募時の名前', $snapshot->payload['name']);
        $this->assertSame('応募時の事業所', $snapshot->payload['experiences'][0]['organization_name']);
        $this->assertContains($qualification->name, $snapshot->payload['qualifications']);

        $jobSeeker->update(['name' => '変更後の名前']);
        $jobSeeker->qualifications()->detach($qualification->id);
        $jobSeeker->experiences()->first()->update(['organization_name' => '変更後の事業所']);

        $snapshot->refresh();

        $this->assertSame('応募時の名前', $snapshot->payload['name'], 'スナップショットの氏名は変わらない');
        $this->assertSame('応募時の事業所', $snapshot->payload['experiences'][0]['organization_name'], 'スナップショットの職務経歴は変わらない');
        $this->assertContains($qualification->name, $snapshot->payload['qualifications'], 'スナップショットの資格は変わらない');
    }

    public function test_未ログインの求職者が会員登録と同時に応募できる(): void
    {
        $jobPosting = JobPosting::factory()->published()->create();

        $this->post(route('public.jobs.apply.store', $jobPosting), [
            'name' => '山田花子',
            'email' => 'hanako@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'message' => 'よろしくお願いします。',
        ])->assertRedirect(route('seeker.mypage'));

        $jobSeeker = JobSeeker::where('email', 'hanako@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($jobSeeker, 'seeker');
        $this->assertDatabaseHas('applications', [
            'job_posting_id' => $jobPosting->id,
            'job_seeker_id' => $jobSeeker->id,
        ]);
    }

    public function test_同じ求人に_2_回応募できない(): void
    {
        $jobPosting = JobPosting::factory()->published()->create();
        $jobSeeker = JobSeeker::factory()->create();

        $this->actingAs($jobSeeker, 'seeker')->post(route('public.jobs.apply.store', $jobPosting), []);

        $this->actingAs($jobSeeker, 'seeker')
            ->post(route('public.jobs.apply.store', $jobPosting), [])
            ->assertSessionHasErrors('application');

        $this->assertSame(1, Application::where('job_posting_id', $jobPosting->id)
            ->where('job_seeker_id', $jobSeeker->id)->count());

        $this->assertSame(1, $jobPosting->fresh()->application_count);
    }

    public function test_流入元が_ur_lパラメータから記録される(): void
    {
        $jobPosting = JobPosting::factory()->published()->create();
        $jobSeeker = JobSeeker::factory()->create();

        $this->get(route('public.jobs.show', $jobPosting).'?ref=indeed');

        $this->actingAs($jobSeeker, 'seeker')->post(route('public.jobs.apply.store', $jobPosting), []);

        $this->assertDatabaseHas('applications', [
            'job_posting_id' => $jobPosting->id,
            'job_seeker_id' => $jobSeeker->id,
            'referrer_source' => 'indeed',
        ]);
    }

    public function test_不正な流入元パラメータは無視される(): void
    {
        $jobPosting = JobPosting::factory()->published()->create();
        $jobSeeker = JobSeeker::factory()->create();

        $this->get(route('public.jobs.show', $jobPosting).'?ref=not-a-real-source');

        $this->actingAs($jobSeeker, 'seeker')->post(route('public.jobs.apply.store', $jobPosting), []);

        $this->assertDatabaseHas('applications', ['referrer_source' => 'direct']);
    }

    public function test_公開中でない求人には応募できない(): void
    {
        $jobPosting = JobPosting::factory()->create(['status' => JobPosting::STATUS_DRAFT]);
        $jobSeeker = JobSeeker::factory()->create();

        $this->actingAs($jobSeeker, 'seeker')
            ->post(route('public.jobs.apply.store', $jobPosting), [])
            ->assertNotFound();

        $this->assertDatabaseMissing('applications', ['job_posting_id' => $jobPosting->id]);
    }
}
