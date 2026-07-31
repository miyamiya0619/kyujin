<?php

namespace Tests\Feature\Public;

use App\Models\JobPosting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * アグリゲーション媒体向け XML フィードの配信(TASKS.md T-14)。
 *
 * リクエストのたびに DB を舐めず、事前生成された静的ファイルを返すだけであることを確認する。
 */
class FeedControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_生成前にアクセスすると404になる(): void
    {
        $this->get(route('feed.indeed'))->assertNotFound();
        $this->get(route('feed.kyujinbox'))->assertNotFound();
        $this->get(route('feed.stanby'))->assertNotFound();
    }

    public function test_生成済みのフィードが配信される(): void
    {
        JobPosting::factory()->published()->create([
            'title' => '配信対象の求人',
            'allow_external_feed' => true,
        ]);

        $this->artisan('feeds:generate');

        $response = $this->get(route('feed.indeed'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $this->assertStringContainsString('配信対象の求人', $response->getContent());
    }
}
