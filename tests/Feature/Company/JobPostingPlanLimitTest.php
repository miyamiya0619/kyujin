<?php

namespace Tests\Feature\Company;

use App\Models\AdminUser;
use App\Models\Company;
use App\Models\CompanyPlanAssignment;
use App\Models\CompanyUser;
use App\Models\JobPosting;
use App\Models\PostingPlan;
use App\Models\SiteSetting;
use App\Models\Workplace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 掲載企業の画面から見た、掲載プラン上限の統合的な振る舞い。
 */
class JobPostingPlanLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_上限に達した企業は求人を下書き保存できるが提出できない(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $workplace = Workplace::factory()->for($company)->create();
        $plan = PostingPlan::factory()->create(['max_job_postings' => 1]);
        CompanyPlanAssignment::create([
            'company_id' => $company->id,
            'posting_plan_id' => $plan->id,
            'starts_at' => now()->subDay(),
        ]);

        // 既に 1 件公開中で枠を使い切っている
        JobPosting::factory()->for($company)->for($workplace)->published()->create();

        // 2 件目は下書きとして保存できる
        $this->actingAs($user, 'company')
            ->post(route('company.job-postings.store'), [
                'workplace_id' => $workplace->id,
                'title' => '2件目の求人',
            ])
            ->assertRedirect(route('company.job-postings.index'));

        $second = JobPosting::where('title', '2件目の求人')->firstOrFail();
        $this->assertSame(JobPosting::STATUS_DRAFT, $second->status);

        // しかし提出はできない
        $this->actingAs($user, 'company')
            ->post(route('company.job-postings.submit', $second))
            ->assertSessionHasErrors('plan');

        $this->assertSame(JobPosting::STATUS_DRAFT, $second->fresh()->status);
    }

    public function test_審査_of_fでも上限に達していれば公開されない(): void
    {
        SiteSetting::current()->update(['requires_review' => false]);

        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $workplace = Workplace::factory()->for($company)->create();
        $plan = PostingPlan::factory()->create(['max_job_postings' => 1]);
        CompanyPlanAssignment::create([
            'company_id' => $company->id,
            'posting_plan_id' => $plan->id,
            'starts_at' => now()->subDay(),
        ]);

        JobPosting::factory()->for($company)->for($workplace)->published()->create();
        $jobPosting = JobPosting::factory()->for($company)->for($workplace)->create(['status' => JobPosting::STATUS_DRAFT]);

        $this->actingAs($user, 'company')
            ->post(route('company.job-postings.submit', $jobPosting))
            ->assertSessionHasErrors('plan');

        $this->assertSame(JobPosting::STATUS_DRAFT, $jobPosting->fresh()->status);
    }

    public function test_事業所の上限に達すると追加登録できない(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $plan = PostingPlan::factory()->create(['max_workplaces' => 1]);
        CompanyPlanAssignment::create([
            'company_id' => $company->id,
            'posting_plan_id' => $plan->id,
            'starts_at' => now()->subDay(),
        ]);

        Workplace::factory()->for($company)->create();

        $this->actingAs($user, 'company')
            ->post(route('company.workplaces.store'), ['name' => '2つ目の事業所'])
            ->assertSessionHasErrors('plan');

        $this->assertDatabaseMissing('workplaces', ['name' => '2つ目の事業所']);
    }

    public function test_承認時にプランの掲載期間から掲載終了日が設定される(): void
    {
        $company = Company::factory()->create();
        $workplace = Workplace::factory()->for($company)->create();
        $plan = PostingPlan::factory()->create(['posting_duration_days' => 14]);
        CompanyPlanAssignment::create([
            'company_id' => $company->id,
            'posting_plan_id' => $plan->id,
            'starts_at' => now()->subDay(),
        ]);

        $jobPosting = JobPosting::factory()->for($company)->for($workplace)->create(['status' => JobPosting::STATUS_PENDING]);
        $admin = AdminUser::factory()->create();

        $this->actingAs($admin, 'admin')->post(route('admin.job-postings.approve', $jobPosting));

        $jobPosting->refresh();
        $this->assertNotNull($jobPosting->expires_at);
        $this->assertEqualsWithDelta(now()->addDays(14)->timestamp, $jobPosting->expires_at->timestamp, 5);
    }

    public function test_ダッシュボードに残枠が表示される(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $plan = PostingPlan::factory()->create(['name' => 'スタンダード', 'max_job_postings' => 5]);
        CompanyPlanAssignment::create([
            'company_id' => $company->id,
            'posting_plan_id' => $plan->id,
            'starts_at' => now()->subDay(),
        ]);

        $this->actingAs($user, 'company')
            ->get(route('company.dashboard'))
            ->assertOk()
            ->assertSee('スタンダード')
            ->assertSee('5 件');
    }
}
