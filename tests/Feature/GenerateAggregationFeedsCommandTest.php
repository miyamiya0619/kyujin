<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * アグリゲーション媒体向け XML フィードの日次生成(TASKS.md T-14)。
 *
 * **非公開・審査待ち・掲載終了・配信不可の求人が一切含まれないこと**が完了条件。
 */
class GenerateAggregationFeedsCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_配信対象の求人だけがフィードに出力される(): void
    {
        $eligible = JobPosting::factory()->published()->create([
            'title' => '配信対象の求人',
            'allow_external_feed' => true,
        ]);

        JobPosting::factory()->published()->create([
            'title' => '配信不可の求人',
            'allow_external_feed' => false,
        ]);

        foreach ([JobPosting::STATUS_DRAFT, JobPosting::STATUS_PENDING, JobPosting::STATUS_CLOSED] as $status) {
            JobPosting::factory()->create([
                'title' => "非公開({$status})",
                'status' => $status,
                'allow_external_feed' => true,
            ]);
        }

        JobPosting::factory()->create([
            'title' => '掲載期限切れ',
            'status' => JobPosting::STATUS_PUBLISHED,
            'published_at' => now()->subMonth(),
            'expires_at' => now()->subDay(),
            'allow_external_feed' => true,
        ]);

        $this->artisan('feeds:generate')->assertSuccessful();

        foreach (['indeed', 'kyujinbox', 'stanby'] as $media) {
            $xml = Storage::disk('local')->get("feeds/{$media}.xml");

            $this->assertStringContainsString('配信対象の求人', $xml, "{$media}: 配信対象の求人が含まれる");
            $this->assertStringNotContainsString('配信不可の求人', $xml, "{$media}: 配信不可の求人は含まれない");
            $this->assertStringNotContainsString('非公開(draft)', $xml);
            $this->assertStringNotContainsString('非公開(pending)', $xml);
            $this->assertStringNotContainsString('非公開(closed)', $xml);
            $this->assertStringNotContainsString('掲載期限切れ', $xml);
        }
    }

    public function test_運営者の設定が_of_fなら全媒体が空になる(): void
    {
        SiteSetting::current()->update(['enables_external_feed' => false]);

        JobPosting::factory()->published()->create([
            'title' => '配信対象のはずの求人',
            'allow_external_feed' => true,
        ]);

        $this->artisan('feeds:generate');

        foreach (['indeed', 'kyujinbox', 'stanby'] as $media) {
            $xml = Storage::disk('local')->get("feeds/{$media}.xml");
            $this->assertStringNotContainsString('配信対象のはずの求人', $xml);
        }
    }

    public function test_各媒体のxmlとして妥当である(): void
    {
        JobPosting::factory()->published()->create(['allow_external_feed' => true]);

        $this->artisan('feeds:generate');

        foreach (['indeed', 'kyujinbox', 'stanby'] as $media) {
            $xml = Storage::disk('local')->get("feeds/{$media}.xml");
            $parsed = simplexml_load_string($xml);

            $this->assertNotFalse($parsed, "{$media}: 妥当な XML であること");
        }
    }

    public function test_urlに流入元パラメータが付く(): void
    {
        JobPosting::factory()->published()->create(['allow_external_feed' => true]);

        $this->artisan('feeds:generate');

        $this->assertStringContainsString('?ref=indeed', Storage::disk('local')->get('feeds/indeed.xml'));
        $this->assertStringContainsString('?ref=kyujinbox', Storage::disk('local')->get('feeds/kyujinbox.xml'));
        $this->assertStringContainsString('?ref=stanby', Storage::disk('local')->get('feeds/stanby.xml'));
    }

    public function test_生成日時と件数がfeedmetaに記録される(): void
    {
        JobPosting::factory()->published()->create(['allow_external_feed' => true]);
        JobPosting::factory()->published()->create(['allow_external_feed' => true]);

        $this->artisan('feeds:generate');

        $meta = json_decode(Storage::disk('local')->get('feeds/indeed.meta.json'), true);

        $this->assertSame(2, $meta['job_count']);
        $this->assertNotEmpty($meta['generated_at']);
    }
}
