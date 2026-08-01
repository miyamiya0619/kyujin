<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\Application;
use App\Models\JobPosting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 運営者のダッシュボード(TASKS.md T-16)。
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_掲載中求人数と今月の応募数が表示される(): void
    {
        $admin = AdminUser::factory()->create();

        JobPosting::factory()->published()->create();
        JobPosting::factory()->published()->create();
        JobPosting::factory()->create(['status' => JobPosting::STATUS_DRAFT]);

        $jobPosting = JobPosting::factory()->published()->create();
        Application::factory()->create([
            'job_posting_id' => $jobPosting->id,
            'company_id' => $jobPosting->company_id,
            'applied_at' => now(),
        ]);
        Application::factory()->create([
            'job_posting_id' => $jobPosting->id,
            'company_id' => $jobPosting->company_id,
            'applied_at' => now()->subMonths(2),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('3 件'); // 掲載中求人数(published x3)
        $response->assertSee('1 件'); // 今月の応募数
    }
}
