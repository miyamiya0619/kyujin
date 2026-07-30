<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 掲載期限切れの自動掲載終了バッチ(SPEC.md 6.1 / 7章)。
 */
class CloseExpiredJobPostingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_掲載期限切れの公開中求人が掲載終了になる(): void
    {
        $expired = JobPosting::factory()->create([
            'status' => JobPosting::STATUS_PUBLISHED,
            'expires_at' => now()->subDay(),
        ]);

        $this->artisan('job-postings:close-expired')->assertSuccessful();

        $this->assertSame(JobPosting::STATUS_CLOSED, $expired->fresh()->status);
    }

    public function test_期限内の公開中求人は変わらない(): void
    {
        $active = JobPosting::factory()->create([
            'status' => JobPosting::STATUS_PUBLISHED,
            'expires_at' => now()->addDay(),
        ]);

        $this->artisan('job-postings:close-expired');

        $this->assertSame(JobPosting::STATUS_PUBLISHED, $active->fresh()->status);
    }

    public function test_掲載期限がない公開中求人は変わらない(): void
    {
        $unlimited = JobPosting::factory()->create([
            'status' => JobPosting::STATUS_PUBLISHED,
            'expires_at' => null,
        ]);

        $this->artisan('job-postings:close-expired');

        $this->assertSame(JobPosting::STATUS_PUBLISHED, $unlimited->fresh()->status);
    }

    public function test_下書きや審査待ちは対象外(): void
    {
        $draft = JobPosting::factory()->create([
            'status' => JobPosting::STATUS_DRAFT,
            'expires_at' => now()->subDay(),
        ]);

        $this->artisan('job-postings:close-expired');

        $this->assertSame(JobPosting::STATUS_DRAFT, $draft->fresh()->status);
    }
}
