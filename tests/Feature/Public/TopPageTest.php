<?php

namespace Tests\Feature\Public;

use App\Models\JobCategory;
use App\Models\JobPosting;
use App\Models\JobSeeker;
use App\Models\Prefecture;
use App\Models\Workplace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_トップページが表示される(): void
    {
        $this->get(route('public.top'))->assertOk();
    }

    public function test_未ログインではヘッダーにログインリンクが出る(): void
    {
        $this->get(route('public.top'))
            ->assertSee('ログイン')
            ->assertDontSee('こんにちは');
    }

    public function test_ログイン中はヘッダーに名前とログアウトが出る(): void
    {
        $jobSeeker = JobSeeker::factory()->create(['name' => '山田太郎']);

        $this->actingAs($jobSeeker, 'seeker')
            ->get(route('public.top'))
            ->assertSee('こんにちは、山田太郎 さん')
            ->assertSee('ログアウト');
    }

    public function test_上位表示の求人がおすすめ求人として表示される(): void
    {
        JobPosting::factory()->published()->create(['title' => 'おすすめ求人', 'is_featured' => true]);
        JobPosting::factory()->published()->create(['title' => '通常求人', 'is_featured' => false]);

        $this->get(route('public.top'))
            ->assertOk()
            ->assertSee('おすすめ求人')
            ->assertSee('通常求人'); // 新着求人としては両方出る
    }

    public function test_審査待ちの求人はトップページに出ない(): void
    {
        JobPosting::factory()->create(['title' => '審査待ち求人', 'status' => JobPosting::STATUS_PENDING]);

        $this->get(route('public.top'))->assertDontSee('審査待ち求人');
    }

    public function test_下書きの求人はトップページに出ない(): void
    {
        JobPosting::factory()->create(['title' => '下書き求人', 'status' => JobPosting::STATUS_DRAFT]);

        $this->get(route('public.top'))->assertDontSee('下書き求人');
    }

    public function test_掲載件数に公開中の求人だけが数えられる(): void
    {
        JobPosting::factory()->published()->count(3)->create();
        JobPosting::factory()->create(['status' => JobPosting::STATUS_PENDING]);

        $this->get(route('public.top'))
            ->assertOk()
            ->assertSee('<span class="count-badge-num">3</span>', false);
    }

    public function test_エリアから探すに都道府県ごとの公開求人件数が出る(): void
    {
        $prefecture = Prefecture::factory()->create(['name' => '東京都', 'region' => '関東']);
        $workplace = Workplace::factory()->create(['prefecture_id' => $prefecture->id]);

        JobPosting::factory()->published()->for($workplace, 'workplace')->count(2)->create();

        $this->get(route('public.top'))
            ->assertOk()
            ->assertSee('エリアから探す')
            ->assertSee('東京都')
            ->assertSee('<span class="browse-count">2</span>', false);
    }

    /**
     * 「◯◯から探す」の件数は公開面の表示そのもの。審査前・掲載終了・期限切れを
     * 数えてしまうと、実在しない求人があるように見せることになる(CLAUDE.md 3.5)。
     */
    public function test_エリア別の件数に公開中でない求人が含まれない(): void
    {
        $prefecture = Prefecture::factory()->create(['name' => '東京都', 'region' => '関東']);
        $workplace = Workplace::factory()->create(['prefecture_id' => $prefecture->id]);

        JobPosting::factory()->published()->for($workplace, 'workplace')->create();

        foreach ([
            JobPosting::STATUS_DRAFT,
            JobPosting::STATUS_PENDING,
            JobPosting::STATUS_REJECTED,
            JobPosting::STATUS_CLOSED,
        ] as $status) {
            JobPosting::factory()->for($workplace, 'workplace')->create(['status' => $status]);
        }

        // 掲載期限切れ(日次バッチが未実行で published のまま残っているケース)
        JobPosting::factory()->for($workplace, 'workplace')->create([
            'status' => JobPosting::STATUS_PUBLISHED,
            'expires_at' => now()->subDay(),
        ]);

        $this->get(route('public.top'))
            ->assertOk()
            ->assertSee('<span class="browse-count">1</span>', false)
            ->assertDontSee('<span class="browse-count">6</span>', false);
    }

    public function test_職種から探すに公開求人の件数が出る(): void
    {
        $jobCategory = JobCategory::create(['code' => 'care_worker', 'name' => '介護職員']);

        JobPosting::factory()->published()->create(['job_category_id' => $jobCategory->id]);
        JobPosting::factory()->create([
            'job_category_id' => $jobCategory->id,
            'status' => JobPosting::STATUS_DRAFT,
        ]);

        $this->get(route('public.top'))
            ->assertOk()
            ->assertSee('職種から探す')
            ->assertSee('介護職員')
            ->assertSee('<span class="browse-count">1</span>', false);
    }

    public function test_夜勤ありの特集に公開求人の件数だけが出る(): void
    {
        JobPosting::factory()->published()->create(['has_night_shift' => true]);
        JobPosting::factory()->create([
            'has_night_shift' => true,
            'status' => JobPosting::STATUS_PENDING,
        ]);

        $this->get(route('public.top'))
            ->assertOk()
            ->assertSee('夜勤ありの求人')
            ->assertSee('<p class="tile-count">1 件</p>', false);
    }

    /**
     * コラム・利用者の声は器だけ用意して機能が未実装(TopController 参照)。
     * ダミーで埋めて公開してしまわないよう、空なら描画されないことを固定する。
     */
    public function test_機能未実装のコラムと利用者の声は表示されない(): void
    {
        JobPosting::factory()->published()->create();

        $this->get(route('public.top'))
            ->assertOk()
            ->assertDontSee('お役立ちコラム')
            ->assertDontSee('ご利用者様の声');
    }
}
