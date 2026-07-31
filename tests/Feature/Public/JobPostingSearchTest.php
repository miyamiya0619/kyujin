<?php

namespace Tests\Feature\Public;

use App\Models\Company;
use App\Models\JobPosting;
use App\Models\Prefecture;
use App\Models\Workplace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 公開メディアの求人検索・詳細。
 *
 * **審査を通っていない求人が一切表示されないこと**が CLAUDE.md 3.5 の絶対ルール。
 */
class JobPostingSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_公開中の求人だけが一覧に表示される(): void
    {
        JobPosting::factory()->published()->create(['title' => '公開中の求人']);

        foreach ([
            JobPosting::STATUS_DRAFT,
            JobPosting::STATUS_PENDING,
            JobPosting::STATUS_REJECTED,
            JobPosting::STATUS_CLOSED,
        ] as $status) {
            JobPosting::factory()->create(['title' => "非公開({$status})", 'status' => $status]);
        }

        $response = $this->get(route('public.jobs.index'));

        $response->assertOk()->assertSee('公開中の求人');

        foreach (['draft', 'pending', 'rejected', 'closed'] as $status) {
            $response->assertDontSee("非公開({$status})");
        }
    }

    public function test_掲載期限が切れた公開中求人はステータス更新前でも表示されない(): void
    {
        // CloseExpiredJobPostingsCommand が未実行でも漏れないことの確認(scopePublished の安全策)
        JobPosting::factory()->create([
            'title' => '期限切れ求人',
            'status' => JobPosting::STATUS_PUBLISHED,
            'expires_at' => now()->subDay(),
        ]);

        $this->get(route('public.jobs.index'))->assertDontSee('期限切れ求人');
    }

    public function test_都道府県で絞り込める(): void
    {
        $tokyo = Prefecture::factory()->create(['name' => '東京都']);
        $osaka = Prefecture::factory()->create(['name' => '大阪府']);

        $tokyoWorkplace = Workplace::factory()->create(['prefecture_id' => $tokyo->id]);
        $osakaWorkplace = Workplace::factory()->create(['prefecture_id' => $osaka->id]);

        JobPosting::factory()->published()->for($tokyoWorkplace, 'workplace')->create(['title' => '東京の求人']);
        JobPosting::factory()->published()->for($osakaWorkplace, 'workplace')->create(['title' => '大阪の求人']);

        $response = $this->get(route('public.jobs.index', ['prefecture_id' => $tokyo->id]));

        $response->assertSee('東京の求人')->assertDontSee('大阪の求人');
    }

    public function test_夜勤ありのみで絞り込める(): void
    {
        JobPosting::factory()->published()->create(['title' => '夜勤ありの求人', 'has_night_shift' => true]);
        JobPosting::factory()->published()->create(['title' => '夜勤なしの求人', 'has_night_shift' => false]);

        $response = $this->get(route('public.jobs.index', ['night_shift_only' => 1]));

        $response->assertSee('夜勤ありの求人')->assertDontSee('夜勤なしの求人');
    }

    public function test_キーワードで絞り込める(): void
    {
        JobPosting::factory()->published()->create(['title' => '介護福祉士募集']);
        JobPosting::factory()->published()->create(['title' => '看護師募集']);

        $response = $this->get(route('public.jobs.index', ['keyword' => '介護福祉士']));

        $response->assertSee('介護福祉士募集')->assertDontSee('看護師募集');
    }

    public function test_審査待ちの求人詳細に直接アクセスすると404になる(): void
    {
        $jobPosting = JobPosting::factory()->create(['status' => JobPosting::STATUS_PENDING]);

        $this->get(route('public.jobs.show', $jobPosting))->assertNotFound();
    }

    public function test_下書きの求人詳細に直接アクセスすると404になる(): void
    {
        $jobPosting = JobPosting::factory()->create(['status' => JobPosting::STATUS_DRAFT]);

        $this->get(route('public.jobs.show', $jobPosting))->assertNotFound();
    }

    public function test_公開中の求人詳細は表示され閲覧数が増える(): void
    {
        $jobPosting = JobPosting::factory()->published()->create(['title' => '詳細表示テスト', 'view_count' => 0]);

        $this->get(route('public.jobs.show', $jobPosting))
            ->assertOk()
            ->assertSee('詳細表示テスト');

        $this->assertSame(1, $jobPosting->fresh()->view_count);
    }

    public function test_求人詳細に_jso_n_l_dが埋め込まれる(): void
    {
        $jobPosting = JobPosting::factory()->published()->create(['title' => 'JSON-LDテスト求人']);

        $response = $this->get(route('public.jobs.show', $jobPosting));

        $response->assertOk();
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type":"JobPosting"', false);
    }

    public function test_htm_lソースに求人内容がそのまま含まれる_ss_rであること(): void
    {
        $jobPosting = JobPosting::factory()->published()->create([
            'title' => 'SSR確認用求人タイトル',
            'description' => 'SSR確認用の仕事内容説明文',
        ]);

        $response = $this->get(route('public.jobs.show', $jobPosting));

        // JS実行なしの生HTMLに直接含まれていること
        $response->assertSee('SSR確認用求人タイトル');
        $response->assertSee('SSR確認用の仕事内容説明文');
    }

    public function test_掲載停止中の企業ページは表示されない(): void
    {
        $company = Company::factory()->suspended()->create();

        $this->get(route('public.companies.show', $company))->assertNotFound();
    }

    public function test_稼働中の企業ページは公開中の求人だけを表示する(): void
    {
        $company = Company::factory()->create(['status' => Company::STATUS_ACTIVE]);
        $workplace = Workplace::factory()->for($company)->create();

        JobPosting::factory()->published()->for($company)->for($workplace)->create(['title' => '公開中求人']);
        JobPosting::factory()->for($company)->for($workplace)->create(['title' => '下書き求人', 'status' => JobPosting::STATUS_DRAFT]);

        $response = $this->get(route('public.companies.show', $company));

        $response->assertOk()->assertSee('公開中求人')->assertDontSee('下書き求人');
    }

    public function test_サイトマップに公開中の求人だけが含まれる(): void
    {
        $published = JobPosting::factory()->published()->create();
        $draft = JobPosting::factory()->create(['status' => JobPosting::STATUS_DRAFT]);

        $response = $this->get(route('public.sitemap'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');

        $xml = $response->getContent();
        $this->assertStringContainsString(route('public.jobs.show', $published), $xml);
        $this->assertStringNotContainsString(route('public.jobs.show', $draft), $xml);
    }

    public function test_サイトマップは有効な_xml(): void
    {
        JobPosting::factory()->published()->create();

        $response = $this->get(route('public.sitemap'));

        $dom = new \DOMDocument;
        $valid = $dom->loadXML($response->getContent());

        $this->assertTrue($valid, 'サイトマップが有効な XML であること');
    }
}
