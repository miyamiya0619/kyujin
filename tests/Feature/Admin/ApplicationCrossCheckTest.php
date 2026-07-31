<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\Application;
use App\Models\Company;
use App\Models\JobPosting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 応募の横断確認(運営者。SPEC.md 5.3)。全掲載企業の応募状況を閲覧できる。
 */
class ApplicationCrossCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_全掲載企業の応募が一覧に表示される(): void
    {
        $admin = AdminUser::factory()->create();

        $companyA = Company::factory()->create(['name' => 'A社']);
        $companyB = Company::factory()->create(['name' => 'B社']);
        $jobA = JobPosting::factory()->for($companyA)->published()->create(['title' => 'A社の求人']);
        $jobB = JobPosting::factory()->for($companyB)->published()->create(['title' => 'B社の求人']);

        Application::factory()->create(['job_posting_id' => $jobA->id, 'company_id' => $companyA->id]);
        Application::factory()->create(['job_posting_id' => $jobB->id, 'company_id' => $companyB->id]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.index'))
            ->assertOk()
            ->assertSee('A社の求人')
            ->assertSee('B社の求人');
    }

    public function test_掲載企業で絞り込める(): void
    {
        $admin = AdminUser::factory()->create();

        $companyA = Company::factory()->create(['name' => 'A社']);
        $companyB = Company::factory()->create(['name' => 'B社']);
        $jobA = JobPosting::factory()->for($companyA)->published()->create(['title' => 'A社の求人']);
        $jobB = JobPosting::factory()->for($companyB)->published()->create(['title' => 'B社の求人']);

        Application::factory()->create(['job_posting_id' => $jobA->id, 'company_id' => $companyA->id]);
        Application::factory()->create(['job_posting_id' => $jobB->id, 'company_id' => $companyB->id]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.index', ['company_id' => $companyA->id]))
            ->assertSee('A社の求人')
            ->assertDontSee('B社の求人');
    }
}
