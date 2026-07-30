<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\JobPosting;
use App\Models\Workplace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 運営者による求人の代行入稿。立ち上げ期に掲載企業へ代わって登録する運用(SPEC.md 11.6)。
 */
class JobPostingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_運営者は任意の企業に求人を代行入稿できる(): void
    {
        $admin = AdminUser::factory()->create();
        $company = Company::factory()->create();
        $workplace = Workplace::factory()->for($company)->create();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.companies.job-postings.store', $company), [
                'workplace_id' => $workplace->id,
                'title' => '代行入稿の求人',
            ])
            ->assertRedirect(route('admin.companies.job-postings.index', $company));

        $this->assertDatabaseHas('job_postings', [
            'company_id' => $company->id,
            'title' => '代行入稿の求人',
        ]);
    }

    public function test_他社の求人を_ur_lで組み替えても編集できない(): void
    {
        $admin = AdminUser::factory()->create();
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $jobPostingB = JobPosting::factory()->for($companyB)->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.companies.job-postings.edit', [$companyA, $jobPostingB]))
            ->assertNotFound();
    }

    public function test_一覧はその企業の求人だけを表示する(): void
    {
        $admin = AdminUser::factory()->create();
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        JobPosting::factory()->for($companyA)->create(['title' => 'A社の求人']);
        JobPosting::factory()->for($companyB)->create(['title' => 'B社の求人']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.companies.job-postings.index', $companyA))
            ->assertOk()
            ->assertSee('A社の求人')
            ->assertDontSee('B社の求人');
    }

    public function test_掲載企業は運営管理画面の代行入稿ルートに入れない(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();

        $this->actingAs($user, 'company')
            ->get(route('admin.companies.job-postings.index', $company))
            ->assertRedirect(route('admin.login'));
    }

    public function test_他社の事業所を指定した代行入稿は失敗する(): void
    {
        $admin = AdminUser::factory()->create();
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $workplaceB = Workplace::factory()->for($companyB)->create();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.companies.job-postings.store', $companyA), [
                'workplace_id' => $workplaceB->id,
                'title' => '不正な代行入稿',
            ])
            ->assertNotFound();
    }
}
