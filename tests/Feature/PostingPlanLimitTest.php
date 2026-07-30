<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyPlanAssignment;
use App\Models\JobPosting;
use App\Models\PostingPlan;
use App\Models\Workplace;
use App\Services\PostingPlanLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 掲載プランの上限判定(SPEC.md 6.1)。
 */
class PostingPlanLimitTest extends TestCase
{
    use RefreshDatabase;

    private PostingPlanLimitService $limits;

    protected function setUp(): void
    {
        parent::setUp();
        $this->limits = app(PostingPlanLimitService::class);
    }

    private function assignPlan(Company $company, PostingPlan $plan, ?string $startsAt = null): void
    {
        CompanyPlanAssignment::create([
            'company_id' => $company->id,
            'posting_plan_id' => $plan->id,
            'starts_at' => $startsAt ?? now()->subDay(),
            'ends_at' => null,
        ]);
    }

    public function test_プラン未割当の企業は無制限扱い(): void
    {
        $company = Company::factory()->create();

        $this->assertNull($this->limits->currentPlan($company));
        $this->assertTrue($this->limits->canSubmitJobPosting($company));
        $this->assertTrue($this->limits->canAddWorkplace($company));
        $this->assertNull($this->limits->remainingJobPostingSlots($company));
    }

    public function test_同時掲載件数の上限に達すると提出できない(): void
    {
        $company = Company::factory()->create();
        $plan = PostingPlan::factory()->create(['max_job_postings' => 2]);
        $this->assignPlan($company, $plan);

        JobPosting::factory()->for($company)->create(['status' => JobPosting::STATUS_PUBLISHED]);
        JobPosting::factory()->for($company)->create(['status' => JobPosting::STATUS_PENDING]);

        $this->assertFalse($this->limits->canSubmitJobPosting($company));
        $this->assertSame(0, $this->limits->remainingJobPostingSlots($company));
    }

    public function test_下書きは同時掲載件数にカウントしない(): void
    {
        $company = Company::factory()->create();
        $plan = PostingPlan::factory()->create(['max_job_postings' => 1]);
        $this->assignPlan($company, $plan);

        JobPosting::factory()->for($company)->create(['status' => JobPosting::STATUS_DRAFT]);
        JobPosting::factory()->for($company)->create(['status' => JobPosting::STATUS_DRAFT]);

        $this->assertTrue($this->limits->canSubmitJobPosting($company), '下書きは枠を消費しない');
    }

    public function test_編集中の求人自身は上限カウントから除外される(): void
    {
        $company = Company::factory()->create();
        $plan = PostingPlan::factory()->create(['max_job_postings' => 1]);
        $this->assignPlan($company, $plan);

        $rejected = JobPosting::factory()->for($company)->create(['status' => JobPosting::STATUS_PENDING]);

        // 自分自身を除外すれば再提出できる(差戻し→再提出のケース)
        $this->assertTrue($this->limits->canSubmitJobPosting($company, excluding: $rejected));
    }

    public function test_事業所登録数の上限に達すると追加できない(): void
    {
        $company = Company::factory()->create();
        $plan = PostingPlan::factory()->create(['max_workplaces' => 1]);
        $this->assignPlan($company, $plan);

        Workplace::factory()->for($company)->create();

        $this->assertFalse($this->limits->canAddWorkplace($company));
        $this->assertSame(0, $this->limits->remainingWorkplaceSlots($company));
    }

    public function test_過去に終了したプランは現在のプランとして扱われない(): void
    {
        $company = Company::factory()->create();
        $oldPlan = PostingPlan::factory()->create(['name' => '旧プラン', 'max_job_postings' => 1]);

        CompanyPlanAssignment::create([
            'company_id' => $company->id,
            'posting_plan_id' => $oldPlan->id,
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subMonth(),
        ]);

        $this->assertNull($this->limits->currentPlan($company), '終了済みのプランは現在のプランではない');
    }

    public function test_新しいプランに切り替わると新しい上限が適用される(): void
    {
        $company = Company::factory()->create();
        $oldPlan = PostingPlan::factory()->create(['max_job_postings' => 1]);
        $newPlan = PostingPlan::factory()->create(['max_job_postings' => 10]);

        CompanyPlanAssignment::create([
            'company_id' => $company->id,
            'posting_plan_id' => $oldPlan->id,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->subDay(),
        ]);
        $this->assignPlan($company, $newPlan, now()->toDateString());

        $this->assertSame($newPlan->id, $this->limits->currentPlan($company)->id);
    }

    public function test_公開時にプランの掲載期間から掲載終了日が計算される(): void
    {
        $company = Company::factory()->create();
        $plan = PostingPlan::factory()->create(['posting_duration_days' => 30]);
        $this->assignPlan($company, $plan);

        $attributes = $this->limits->publishAttributes($company);

        $this->assertSame(JobPosting::STATUS_PUBLISHED, $attributes['status']);
        $this->assertNotNull($attributes['expires_at']);
        $this->assertEqualsWithDelta(
            now()->addDays(30)->timestamp,
            $attributes['expires_at']->timestamp,
            5
        );
    }

    public function test_掲載期間が無期限のプランは掲載終了日を設定しない(): void
    {
        $company = Company::factory()->create();
        $plan = PostingPlan::factory()->create(['posting_duration_days' => null]);
        $this->assignPlan($company, $plan);

        $attributes = $this->limits->publishAttributes($company);

        $this->assertNull($attributes['expires_at']);
    }

    public function test_上位表示プランの求人はis_featuredがtrueになる(): void
    {
        $company = Company::factory()->create();
        $plan = PostingPlan::factory()->create(['is_featured' => true]);
        $this->assignPlan($company, $plan);

        $attributes = $this->limits->publishAttributes($company);

        $this->assertTrue($attributes['is_featured']);
    }
}
