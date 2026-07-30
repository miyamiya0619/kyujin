<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\PostingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostingPlanManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_運営者は掲載プランを作成できる(): void
    {
        $admin = AdminUser::factory()->create();

        $this->actingAs($admin, 'admin')->post(route('admin.posting-plans.store'), [
            'name' => 'プレミアム',
            'max_job_postings' => 20,
            'posting_duration_days' => 60,
            'is_featured' => '1',
            'is_enabled' => '1',
        ])->assertRedirect(route('admin.posting-plans.index'));

        $this->assertDatabaseHas('posting_plans', [
            'name' => 'プレミアム',
            'max_job_postings' => 20,
            'is_featured' => true,
        ]);
    }

    public function test_プラン名が無ければ作成できない(): void
    {
        $admin = AdminUser::factory()->create();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.posting-plans.store'), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('posting_plans', 0);
    }

    public function test_掲載企業はプラン管理画面に入れない(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();

        $this->actingAs($user, 'company')
            ->get(route('admin.posting-plans.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_企業にプランを割り当てると割当履歴に残る(): void
    {
        $admin = AdminUser::factory()->create();
        $company = Company::factory()->create();
        $plan = PostingPlan::factory()->create(['name' => 'ベーシック']);

        $this->actingAs($admin, 'admin')->post(route('admin.companies.plan-assignments.store', $company), [
            'posting_plan_id' => $plan->id,
            'starts_at' => now()->toDateString(),
        ])->assertRedirect(route('admin.companies.show', $company));

        $this->assertDatabaseHas('company_plan_assignments', [
            'company_id' => $company->id,
            'posting_plan_id' => $plan->id,
            'ends_at' => null,
        ]);
    }

    public function test_新しいプランを割り当てると前のプランが終了する(): void
    {
        $admin = AdminUser::factory()->create();
        $company = Company::factory()->create();
        $oldPlan = PostingPlan::factory()->create(['name' => '旧プラン']);
        $newPlan = PostingPlan::factory()->create(['name' => '新プラン']);

        $company->planAssignments()->create([
            'posting_plan_id' => $oldPlan->id,
            'starts_at' => now()->subMonth(),
            'ends_at' => null,
        ]);

        $this->actingAs($admin, 'admin')->post(route('admin.companies.plan-assignments.store', $company), [
            'posting_plan_id' => $newPlan->id,
            'starts_at' => now()->toDateString(),
        ]);

        $oldAssignment = $company->planAssignments()->where('posting_plan_id', $oldPlan->id)->first();
        $this->assertNotNull($oldAssignment->ends_at, '古いプランには終了日が設定されること');

        $newAssignment = $company->planAssignments()->where('posting_plan_id', $newPlan->id)->first();
        $this->assertNull($newAssignment->ends_at, '新しいプランは終了日なしで割り当てられること');
    }
}
