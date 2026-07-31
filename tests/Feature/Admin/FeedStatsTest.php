<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\Application;
use App\Models\Company;
use App\Models\JobPosting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * 運営者管理画面での媒体別のアグリゲーション効果(TASKS.md T-14)。
 */
class FeedStatsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_媒体別の応募件数とフィード生成状況が表示される(): void
    {
        $admin = AdminUser::factory()->create();
        $company = Company::factory()->create();

        $jobPosting = JobPosting::factory()->for($company)->published()->create(['allow_external_feed' => true]);

        Application::factory()->create([
            'job_posting_id' => $jobPosting->id,
            'company_id' => $company->id,
            'referrer_source' => 'indeed',
        ]);
        Application::factory()->create([
            'job_posting_id' => $jobPosting->id,
            'company_id' => $company->id,
            'referrer_source' => 'indeed',
        ]);
        Application::factory()->create([
            'job_posting_id' => $jobPosting->id,
            'company_id' => $company->id,
            'referrer_source' => 'direct',
        ]);

        $this->artisan('feeds:generate');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.feeds.index'));

        $response->assertOk();
        $response->assertSee('Indeed');
        $response->assertSee('1 件'); // indeed の配信中求人件数
        $response->assertSee('2 件'); // indeed の応募件数
    }

    public function test_フィード未生成の場合は未生成と表示される(): void
    {
        $admin = AdminUser::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.feeds.index'))
            ->assertOk()
            ->assertSee('未生成');
    }
}
