<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\JobPosting;
use App\Models\SiteSetting;
use App\Models\Workplace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * パッケージ販売プランの上限(SPEC.md 6.2)。
 * あなたが顧客(メディア運営者)に売った契約プランの全体上限であり、
 * 掲載企業ごとの掲載プラン(PostingPlan)とは別軸。
 */
class PackagePlanLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_上限未設定なら無制限(): void
    {
        $setting = SiteSetting::current();

        $this->assertTrue($setting->canAddMoreCompanies());
        $this->assertTrue($setting->canAddMoreJobPostings());
    }

    public function test_掲載企業数の上限に達すると運営者は企業を登録できない(): void
    {
        SiteSetting::current()->update(['max_companies' => 1]);
        Company::factory()->create();

        $admin = AdminUser::factory()->create();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.companies.store'), ['name' => '2社目', 'status' => Company::STATUS_ACTIVE])
            ->assertSessionHasErrors('plan');

        $this->assertDatabaseMissing('companies', ['name' => '2社目']);
    }

    public function test_求人数の上限に達すると求人を登録できない(): void
    {
        SiteSetting::current()->update(['max_job_postings' => 1]);

        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $workplace = Workplace::factory()->for($company)->create();
        JobPosting::factory()->for($company)->for($workplace)->create();

        $this->actingAs($user, 'company')
            ->post(route('company.job-postings.store'), [
                'workplace_id' => $workplace->id,
                'title' => '上限超過の求人',
            ])
            ->assertSessionHasErrors('plan');

        $this->assertDatabaseMissing('job_postings', ['title' => '上限超過の求人']);
    }
}
